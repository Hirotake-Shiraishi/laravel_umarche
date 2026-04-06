<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner; //エロクアント Eloquent
use Illuminate\Support\Facades\DB; //クエリビルダ QueryBilder
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use PhpParser\Node\Stmt\TryCatch;

use Throwable; // Throwableインターフェイスの読み込み
use Illuminate\Support\Facades\Log; // Logファサードの読み込み
use App\Models\Shop; // Shopモデルの読み込み

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

        // 例外をかけつつ、トランザクションをかける。
        // Throwable（PHP7以降の機能）
        // ・use文でインポート、または、頭に\をつけることで使用可能。
        try{
            // トランザクションは、引数で無名関数(クロージャー)を受け取る。
            // フォームで入力されて渡ってきた値 $request をクロージャーに渡すには、
            // use($request) を記載することで、クロージャー内で、$request 使用可能となる。
            DB::transaction(function () use($request) {

                // Ownerの登録処理をトランザクション内に移設。
                // 変数として設定することで、クラスのインスタンス化
                $owner = Owner::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                // Shopの登録処理
                Shop::create([
                    'owner_id' => $owner->id,
                    'name' => '店名を入力してください',
                    'information' => '',
                    'filename' => '',
                    'is_selling' => true
                ]);

            // 第二引数:トランザクションを再試行する回数
            }, 2);

        } catch (Throwable $e) {
            Log::error($e);
            throw $e;
        }

        return redirect()
            ->route('admin.owners.index')
            ->with(['message' => 'オーナーを登録しました', 'status' => 'info']);
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


    /**
     * オーナー情報を更新
     *
     * （指摘#4 / 課題4: バリデーション強化）
     *
     * 修正前：update() にバリデーションがなく、name/email が空でも保存でき、メール形式・一意性チェックもなかった。
     * また password が空のとき Hash::make('') で空ハッシュが保存されていた。
     *
     * 修正後: validate 追加（email は unique で自分自身の ID を除外）、パスワードは入力時のみ更新。
     */
    public function update(Request $request, $id)
    {
        // バリデーションをかける
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:owners,email,' . $id, // 自分自身の ID を除外
            'password' => 'nullable|string|confirmed|min:8',
        ]);

        $owner = Owner::findOrFail($id);
        $owner->name = $request->name;
        $owner->email = $request->email;
        // パスワードは入力があるときだけハッシュして保存（空のときは更新しない）
        if($request->filled('password')){
            $owner->password = Hash::make($request->password);
        }
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
