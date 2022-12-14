<?php

namespace App\Http\Controllers;


use App\Models\AdModAccount;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;




class UserController extends Controller
{
    private $user;
    public $role;
    public function __construct(User $user, Role $role)
    {
        $this->user = $user;
        $this->role = $role;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pageConfigs = ['pageHeader' => false];
        $roles = $this->role->all();

        return view('content.user.index', ['pageConfigs' => $pageConfigs, 'roles'=>$roles ]);
    }

    public function delete($id)
    {
        User::find($id)->delete();
        return response()->json(['success'=>'Xóa người dùng.']);
    }

    public function callAction($method, $parameters)
    {
        return parent::callAction($method, array_values($parameters));
    }
    public function post_user_list(Request $request)
    {

        $user = User::all();
        $arrayjson = array();
        $arrayreturn = array();
        foreach ($user as $key => $value) {
//            $btn = '<a href="javascript:void(0)" onclick="editUser('.$value->id.')" class="btn btn-warning">Edit</i></a>';
            $btn = "<a href='user/get_add_user/$value->id' class='btn btn-warning'>Edit</i></a>";
            $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$value->id.'" data-original-title="Delete" class="btn btn-danger deleteUser">Del</i></a>';


            $arrayjson[$key]['id'] = $key + 1;
            $arrayjson[$key]['name'] = $value->name;
            $arrayjson[$key]['email'] = $value->email;
            $arrayjson[$key]['action'] =  $btn;
        }

        return json_encode(array('data' => $arrayjson, 'draw' => 1, 'recordsTotal' => count($arrayreturn), 'recordsFiltered' => count($arrayreturn)));
    }
    function get_add_user($id)
    {
        if (isset($id)) {
            $user = User::where('id', $id)->first();

            $roles = $this->role->get();
            $roleSelect =  $user->roles;
            $admod_account =  AdModAccount::all();
//            dd($admod_account);
            $admodChecked = $user->admods()->get();


            if (isset($user)) {
                return view('content.user.edit',[
                    'user'=>$user,
                    'roles'=>$roles ,
                    'roleSelect'=>$roleSelect,
                    'admod_account'=>$admod_account,
                    'admodChecked'=>$admodChecked
                ]);
//                return response()->json(['success' => 'Cập nhật thành công', $user,$roleOfUser]);
            }
            return response()->json(['errors' => 'ID không tồn tại']);
        }
    }
    function post_add_user(Request $request)
    {


        if (isset($request->id)) {
            $rules = [
                'name' => 'required|unique:users,name,'.$request->id.',id',
                'email' => 'required|unique:users,email,'.$request->id.',id',

            ];
            $message = [
                'email.required' => 'Email không được để trống',
                'name.required' => 'Tên không được để trống',
                'name.unique'=>'Tên người dùng đã tồn tại',
                'email.unique'=>'Email đã tồn tại',
            ];
            $error = Validator::make($request->all(), $rules, $message);
            if ($error->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $error->getMessageBag()
                ]);
            }
            try {
                if($request->password){
                    $user = User::where('id', $request->id)->first();
                    $user->name= $request->name;
                    $user->email = $request->email;
                    $user->password = bcrypt($request->password);
                    $user->save();
                }
                else{
                    $user = User::where('id', $request->id)->first();
                    $user->name= $request->name;
                    $user->email = $request->email;
                    $user->save();
                }
                $roleIds = $request->role_id;
                $admodIds = $request->admodId;
                $user = $this->user->find($request->id);
                $user->roles()->sync($roleIds);
                $user->admods()->sync($admodIds);
                DB::commit();
                return response()->json(['success'=>'Cập nhật thành công']);
            } catch (\Exception $exception) {
                DB::rollBack();
                Log::error('Message :' . $exception->getMessage() . '--- Line: ' . $exception->getLine());
            }


        } else {
            $rules = [
                'name' => 'required|unique:users,name',
                'email' => 'required|unique:users,email',
                'password'=>'required'
            ];
            $message = [
                'email.required' => 'Email không được để trống',
                'name.required' => 'Tên không được để trống',
                'name.unique'=>'Tên người dùng đã tồn tại',
                'email.unique'=>'Email đã tồn tại',
                'password.required' => 'Mật khẩu không để trống.'
            ];
            $error = Validator::make($request->all(), $rules, $message);

            if ($error->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $error->getMessageBag()
                ]);
            }
            try {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' =>bcrypt($request->password),
                ]);

                $roleIds = $request->role_id;
                $user->roles()->attach($roleIds);
                DB::commit();
                return response()->json(['success' => 'Thêm mới thành công']);

            } catch (\Exception $exception) {
                DB::rollBack();
                Log::error('Message :' . $exception->getMessage() . '--- Line: ' . $exception->getLine());
            }
        }
    }

//    public function edit($id)
//    {
//        $user = $this->user->find($id);
//        $roles = $this->role->get();
//        $roleSelect =  $user->roles;
//        $admod_account =  AdModAccount::all();
//        dd($admod_account);
//        $permissionsParent = $this->permision->where('parent_id',0)->get();
//        $admodChecked = $admod_account;

//        return view('content.user.edit',[
//            'user'=>$user,
//            'roles'=>$roles ,
//            'roleSelect'=>$roleSelect,
//            'admod_account'=>$admod_account
//        ]);
//
////        $role = Role::find($id);
////        $permissionOfRole = $role->permissions;
////
////        return response()->json([$role,$permissionOfRole]);
//    }

}
