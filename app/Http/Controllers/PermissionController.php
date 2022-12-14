<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class PermissionController extends Controller
{


//    private $role;
    private $permission;
    public function __construct(Permission $permission, Role $role)
    {
        $this->permission = $permission;
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
        return view('content.permission.index', ['pageConfigs' => $pageConfigs]);
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
        $totalRecords = Permission::select('count(*) as allcount')->count();
        $totalRecordswithFilter = Permission::select('count(*) as allcount')
            ->where('parent_id', '=', '1')
            ->where('name', 'like', '%' . $searchValue . '%')
            ->orWhere('display_name', 'like', '%' . $searchValue . '%')
            ->count();

        // Get records, also we have included search filter as well
        $records = Permission::orderBy($columnName, $columnSortOrder)
            ->where('parent_id', '=', '1')
            ->where('name', 'like', '%' . $searchValue . '%')
            ->orWhere('display_name', 'like', '%' . $searchValue . '%')
            ->select('*')
            ->skip($start)
            ->take($rowperpage)
            ->get();


        $data_arr = array();

        foreach ($records as $record) {
//            $btn = '<a href="javascript:void(0)" onclick="editRole('.$record->id.')" class="btn btn-warning">Edit</i></a>';
//            $btn = "<a href='role/edit/$record->id' class='btn btn-warning'>Edit</i></a>";
//            $btn = ' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$record->id.'" data-original-title="Delete" class="btn btn-danger deleteRole">Del</i></a>';


            $data_arr[] = array(
                "id" => $record->id,
                "name" => $record->name,
                "display_name" => $record->display_name,
//                "action" => $btn,
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

        $permission = Permission::create([
            'name' =>$request->module_parent,
            'display_name'=>$request->module_parent,
            'parent_id' => 0
        ]);

        foreach ($request->module_child as $item){
            Permission::create([
                'name'=> $item.' '.$request->module_parent,
                'display_name'=>$item.' '.$request->module_parent,
                'key_code' => str_replace('-','_',Str::slug($item.'_'.$request->module_parent)),
                'parent_id' => $permission->id
            ]);
        }
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
        $role = Permission::find($id);

        return response()->json([$role]);
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

        $id = $request->permission_id;
        $rules = [
            'name' =>'required|unique:permissions,name,'.$id.',id',
            'display_name' =>'required',

        ];
        $message = [
            'name.unique'=>'Tên quyền đã tồn tại',
            'name.required'=>'Tên quyền không để trống',
            'display_name.required'=>'Mô tả không để trống',
        ];
//
        $error = Validator::make($request->all(),$rules, $message );
        if($error->fails()){
            return response()->json(['errors'=> $error->errors()->all()]);
        }
            $role = $this->role->find($id)->update([
                'name' => $request->name,
                'display_name'=> $request->name,
                'key_code' =>str_replace('-','_',Str::slug($request->name))
            ]);
        $permissionIds = $request->permissionId;
        $role->permissions()->attach($permissionIds);
        return response()->json(['success'=>'Cập nhật thành công']);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        Permission::find($id)->delete();
        return response()->json(['success'=>'Xóa thành công.']);
    }

    public function callAction($method, $parameters)
    {
        return parent::callAction($method, array_values($parameters));
    }

}
