<?php

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use crmeb\services\JsonService;
use crmeb\services\SystemConfigService;
use app\admin\model\system\SystemStore as SystemStoreModel;
use crmeb\services\UtilService;

/**
 * 门店管理控制器
 * Class SystemAttachment
 * @package app\admin\controller\system
 *
 */
class SystemStore extends AuthController
{

    /*
     * 门店设置
     * */
    public function index()
    {
        $store = SystemStoreModel::where('is_show',1)->where('is_del',0)->find();
        $storeData = '{}';
        $id = 0;
        if($store){
            $storeData = json_encode($store->toArray());
            $id = $store->id;
        }
        $this->assign(compact('storeData','id'));
        return $this->fetch();
    }

    /*
     * 位置选择
     * */
    public function select_address()
    {
        $key = SystemConfigService::get('tengxun_map_key');
        $this->assign(compact('key'));
        return $this->fetch();
    }

    /*
     * 保存修改门店信息
     * param int $id
     * */
    public function save($id = 0)
    {
        $data = UtilService::postMore([
            ['name',''],
            ['image',''],
            ['phone',''],
            ['address',''],
            ['detailed_address',''],
            ['latlng',''],
        ]);
        SystemStoreModel::beginTrans();
        try{
            $data['latlng'] = explode(',',$data);
            if(!isset($data['latlng'][0]) || !isset($data['latlng'][1])) return JsonService::fail('请选择门店位置');
            $data['latitude'] = $data['latlng'][0];
            $data['longitude'] = $data['latlng'][1];
            unset($data['latlng']);
            if($id){
                if(SystemStoreModel::where('id',$id)->update($data)){
                    SystemStoreModel::commitTrans();
                    return JsonService::success('修改成功');
                }else{
                    SystemStoreModel::rollbackTrans();
                    return JsonService::fail('修改失败或者您没有修改什么！');
                }
            }else{
                $data['add_time'] = time();
                if(SystemStoreModel::create($data)){
                    SystemStoreModel::commitTrans();
                    return JsonService::success('保存成功');
                }else{
                    SystemStoreModel::rollbackTrans();
                    return JsonService::fail('保存失败！');
                }
            }
        }catch (\Exception $e){
            SystemStoreModel::rollbackTrans();
            return JsonService::fail($e->getMessage());
        }
    }
}