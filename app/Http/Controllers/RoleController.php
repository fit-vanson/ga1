<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{

    private $permision;
    public $role;


    public function __construct(Permission $permision, Role $role)
    {
        $this->permision = $permision;
        $this->role = $role;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pageConfigs = ['pageHeader' => false];
        return view('content.role.index', ['pageConfigs' => $pageConfigs]);
    }

    public function getIndex(Request $request)
    {


        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // total number of rows per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index

        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value


        // Total records
        $totalRecords = Role::select('count(*) as allcount')->count();
        $totalRecordswithFilter = Role::select('count(*) as allcount')
            ->where('roles.name', 'like', '%' . $searchValue . '%')
            ->orWhere('roles.display_name', 'like', '%' . $searchValue . '%')
            ->count();

        // Get records, also we have included search filter as well
        $records = Role::orderBy($columnName, $columnSortOrder)
            ->where('roles.name', 'like', '%' . $searchValue . '%')
            ->orWhere('roles.display_name', 'like', '%' . $searchValue . '%')
            ->select('*')
            ->skip($start)
            ->take($rowperpage)
            ->get();

        $data_arr = array();

        foreach ($records as $record) {
//            $btn = '<a href="javascript:void(0)" onclick="editRole('.$record->id.')" class="btn btn-warning">Edit</i></a>';
            $btn = "<a href='role/edit/$record->id' class='btn btn-warning'>Edit</i></a>";
            $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$record->id.'" data-original-title="Delete" class="btn btn-danger deleteRole">Del</i></a>';


            $data_arr[] = array(
                "id" => $record->id,
                "name" => $record->name,
                "display_name" => $record->display_name,
                "action" => $btn,
            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr,
        );

        echo json_encode($response);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request  $request)
    {

        $rules = [
            'name' =>'required|unique:roles,name,',
        ];
        $message = [
            'name.unique'=>'Tên vai trò đã tồn tại',
            'name.required'=>'Tên vai trò không để trống',
        ];

        $error = Validator::make($request->all(),$rules, $message );

        if ($error->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $error->getMessageBag()
            ]);
        }
        if(isset($request->display_name)){
            $display_name = $request->display_name;
        }else{
            $display_name = $request->name;
        }
            $role = $this->role->create([
                'name' => $request->name,
                'display_name'=> $display_name,
            ]);
            $permissionIds = $request->permission_id;

            $role->permissions()->attach($permissionIds);

            return response()->json(['success'=>'Thêm mới thành công']);


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
//
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $role = $this->role->find($id);
        $permissionsParent = $this->permision->where('parent_id',0)->get();
        $permissionsChecked = $role->permissions;
        return view('content.role.edit',[
            'role'=>$role,
            'permissionsParent'=>$permissionsParent,
            'permissionsChecked'=>$permissionsChecked
        ]);

//        $role = Role::find($id);
//        $permissionOfRole = $role->permissions;
//
//        return response()->json([$role,$permissionOfRole]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        $id = $request->id;
        $rules = [
            'name' =>'required|unique:roles,name,'.$id.',id',
            'display_name' =>'required',

        ];
        $message = [
            'name.unique'=>'Tên vai trò đã tồn tại',
            'name.required'=>'Tên quyền không để trống',
            'display_name.required'=>'Mô tả không để trống',
        ];
//
        $error = Validator::make($request->all(),$rules, $message );
        if ($error->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $error->getMessageBag()
            ]);
        }
        $this->role->find($id)->update([
            'name' => $request->name,
            'display_name'=> $request->display_name,
        ]);
        $permissionIds = $request->permissionId;
        $role = $this->role->find($id);
        $role->permissions()->sync($permissionIds);
        return response()->json(['success'=>'Update thành công']);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        Role::find($id)->delete();
        return response()->json(['success'=>'Xóa thành công.']);
    }

    public function callAction($method, $parameters)
    {
        return parent::callAction($method, array_values($parameters));
    }

}
