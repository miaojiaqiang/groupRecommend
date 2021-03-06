<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/12/26
 * Time: 9:32
 */

namespace App\Api\Controllers\Manage\User;


use App\Api\Controllers\Manage\BaseController;
use App\Api\Requests\Manage\User\GroupRequest;
use App\Api\TransFormers\Manage\User\GroupTransformer;
use App\Models\Group;
use EasyWeChat\Foundation\Application;
use GuzzleHttp\Client;
use Illuminate\Http\File;
use Illuminate\Http\Request;

class GroupController extends BaseController
{

    public function index(GroupRequest $request)
    {
        $keyword = $request->get('keyword');
        $group = Group::query()
            ->where(function ($query) use ($keyword) {
                if(is_numeric($keyword)){
                    $query->where('id',$keyword);
                }elseif($keyword){
                    $query->where('name','like','%'.$keyword.'%');
                }
            })
            ->latest()->orderBy('order')->paginate(10);
        return $this->response->paginator($group, new GroupTransformer());
    }

    public function store(GroupRequest $request)
    {
        $data = $request->only('master','name', 'desc', 'address', 'latitude', 'longitude', 'wx', 'logo', 'qr_code');
        $data['order'] = $request->get('order',1);
        $data['ratio_a'] = $request->get('ratio_a')?:0;
        $data['ratio_b'] = $request->get('ratio_b')?:0;
        $data['ratio_c'] = $request->get('ratio_c')?:0;
        $data['ratio_d'] = $request->get('ratio_d')?:0;
        $data['district_a'] = $request->get('district_a','');
        $data['district_b'] = $request->get('district_b','');
        $data['district_c'] = $request->get('district_c','');
        $data['district_d'] = $request->get('district_d','');
        $data['latitude_a'] = $request->get('latitude_a',null);
        $data['latitude_b'] = $request->get('latitude_b',null);
        $data['latitude_c'] = $request->get('latitude_c',null);
        $data['longitude_a'] = $request->get('longitude_a',null);
        $data['longitude_b'] = $request->get('longitude_b',null);
        $data['longitude_c'] = $request->get('longitude_c',null);
        $media = $this->uploadToWX($request->get('qr_code'));
        $data['media_id'] = $media['media_id'];
        $group = new Group();
        foreach($data as $k=>$v){
            $group->{$k} = $v;
        }
        \DB::beginTransaction();
        try{
            $group->save();
            $group->Businesses()->sync($request->get('business_ids'));
        }catch(\Exception $exception){
            \DB::rollback();
            return $this->response->error($exception->getMessage(),500);
        }
        \DB::commit();
        return $this->response->created();
    }

    public function show(GroupRequest $request,$id)
    {
        $info = Group::where('id', $id)->first();
        if(!$info)
            return $this->response->errorNotFound();

        return $this->response->item($info, new GroupTransformer('info'));
    }

    public function update(GroupRequest $request,$id)
    {
        $group = Group::where('id', $id)->first();
        if(!$group)
            return $this->response->errorNotFound();
        $data = $request->only('master','name', 'desc', 'address', 'latitude', 'longitude', 'wx', 'logo', 'qr_code');
        $data['order'] = $request->get('order',1);
        $data['ratio_a'] = $request->get('ratio_a')?:0;
        $data['ratio_b'] = $request->get('ratio_b')?:0;
        $data['ratio_c'] = $request->get('ratio_c')?:0;
        $data['ratio_d'] = $request->get('ratio_d')?:0;
        $data['district_a'] = $request->get('district_a','');
        $data['district_b'] = $request->get('district_b','');
        $data['district_c'] = $request->get('district_c','');
        $data['district_d'] = $request->get('district_d','');
        $data['latitude_a'] = $request->get('latitude_a',null);
        $data['latitude_b'] = $request->get('latitude_b',null);
        $data['latitude_c'] = $request->get('latitude_c',null);
        $data['longitude_a'] = $request->get('longitude_a',null);
        $data['longitude_b'] = $request->get('longitude_b',null);
        $data['longitude_c'] = $request->get('longitude_c',null);
        $media = $this->uploadToWX($request->get('qr_code'));
        $data['media_id'] = $media['media_id'];
        foreach($data as $k=>$v){
            $group->{$k} = $v;
        }
        \DB::beginTransaction();
        try{
            $group->save();
            $group->Businesses()->sync($request->get('business_ids'));
        }catch(\Exception $exception){
            \DB::rollback();
            return $this->response->error($exception->getMessage(),500);
        }
        \DB::commit();
        return $this->response->created();

    }

    public function destroy(GroupRequest $request, $id)
    {
        $group = Group::where('id', $id)->first();
        if(!$group)
            return $this->response->errorNotFound();
        \DB::beginTransaction();
        try {
            $group->businesses()->sync([]);
            $group->delete();
        } catch (\Exception $exception) {
            \DB::rollback();
            return $this->response->error($exception->getMessage(),500);
        }
        \DB::commit();
        return $this->response->noContent();
    }

    public function search(GroupRequest $request)
    {
        $keyword = $request->get('keyword');
        $lists = Group::where('name','like','%'.$keyword.'%')->get();
        return $this->response->collection($lists,new GroupTransformer('lists'));
    }

    public function uploadToWX($image)
    {
        $options = [
            'app_id'=> env('APP_ID'),
            'secret'=> env('APP_SECRET'),
        ];
        $app = new Application($options);
        $accessToken = $app->access_token;
        $token = $accessToken->getToken();
        $client = new Client();
        $url =  get_upload_url($image);
        $image = file_get_contents($url);
        $tmpFile = tempnam('','');
        file_put_contents($tmpFile,$image);
        $file = fopen($tmpFile,'r');
        $r = $client->request('POST', 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$token.'&type=image', [
             'multipart' =>[
                 [
                    'name'      =>  'media',
                    'contents'  =>  $file,
                     'filename' =>  'groupQRCode.jpg',
                ]
             ]
        ]);
        unlink($tmpFile);
        $content = $r->getBody()->getContents();
        $content = json_decode($content,true);
        return $content;
    }
}