<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Owner; //エロクアント Eloquent
use Illuminate\Support\Facades\DB; //クエリビルダ QueryBilder

use Carbon\Carbon;

use Illuminate\Support\Facades\Hash;

class OwnersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }


    public function index()
    {
        // $date_now = Carbon::now();
        // $date_parse = Carbon::parse(now());

        // echo $date_now, '<br>' ;
        // echo $date_parse, '<br>';
        // echo $date_now->year, '<br>' ;

        // $e_all = Owner::all();
        // $q_get = DB::table('owners')->select('name', 'created_at')->get();
        // $q_first = DB::table('owners')->select('name')->first();
        // $c_test = collect([
        //     'name' => 'てすと'
        // ]);

        // var_dump($q_first);
        // dd($e_all, $q_get, $q_first, $c_test);

        $owners = Owner::select('id', 'name', 'email', 'created_at')
            ->paginate(3);

        return view('admin.owners.index', compact('owners'));
    }

    public function create()
    {
        return view('admin.owners.create');
    }


    public function store(Request $request)
    {
        // $request->name;
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:owners',
            'password' => 'required|string|confirmed|min:8',
        ]);

        Owner::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()
            ->route('admin.owners.index')
            ->with(['message' => 'オーナーを登録しました', 'status' => 'alert']);
    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        $owner = Owner::findOrFail($id);
        // dd($owner);
        return view('admin.owners.edit', compact('owner'));
    }


    public function update(Request $request, $id)
    {
        $owner = Owner::findOrFail($id);
        $owner->name = $request->name;
        $owner->email = $request->email;
        $owner->password = Hash::make($request->password);
        $owner->save();

        return redirect()->route('admin.owners.index')
            ->with(['message' => 'オーナー情報を更新しました。', 'status' => 'info']);
    }


    public function destroy($id)
    {
        // dd('削除処理');
        Owner::findOrFail($id)->delete(); //ソフトデリート

        return redirect()->route('admin.owners.index')
            ->with(['message' => 'オーナー情報を削除しました。', 'status' => 'alert']);
    }

    // 期限切れオーナー　一覧表示
    public function expiredOwnerIndex()
    {
        $expiredOwners = Owner::onlyTrashed()->get();
        return view(
            'admin.expired-owners',
            compact('expiredOwners')
        );
    }

    // 期限切れオーナー　物理削除
    public function expiredOwnerDestroy($id)
    {
        Owner::onlyTrashed()->findOrFail($id)->forceDelete();
        return redirect()->route('admin.expired-owners.index')->with(['message' => 'オーナー情報を完全に削除しました。', 'status' => 'alert']);
    }
}
