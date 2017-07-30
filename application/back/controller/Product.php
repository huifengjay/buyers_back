<?php

namespace app\back\controller;

use Symfony\Component\Yaml\Tests\B;
use think\Controller;
use think\Db;
use think\Request;
use app\back\model\Product as ProductModel;
use think\Loader;
use think\Session;
use app\back\model\Sku as SkuModel;
use app\back\model\StockStatus as StockStatusModel;
use app\back\model\LengthUnit as LengthUnitModel;
use app\back\model\WeightUnit as WeightUnitModel;
use app\back\model\Tax as TaxModel;
use app\back\model\Brand as BrandModel;
use app\back\model\Category as CategoryModel;
use app\back\model\CategoryProduct as CategoryProductModel;
use app\back\model\Type as TypeModel;
use app\back\model\Attribute as AttributeModel;
use app\back\model\ProductAttribute as ProductAttributeModel;
use app\back\model\Group as GroupModel;
/**
 * Class Product
 * 产品管理控制器
 * @package app\back\controller
 */
class Product extends Controller
{
    /**
     * @return mixed
     * 设置 = 添加和更新
     * @param $id int
     */
    public function set($id=null)
    {
        $request = Request::instance();// request()

        // 获取模型, 如果是添加获取新模型, 如果是更新获取id对应的模型
        if (is_null($id)) {
            $model = new ProductModel();
        } else {
            $model = ProductModel::get($id);
        }

        // 如果当前为get请求
        if ($request->isGet()) {
            // 直接渲染模板
            // 如果是更新, 则需要分配当前所更新的数据到模板
            $data = Session::has('data') ? Session::get('data') : $model->getData();

            $selected_categories = is_null($id) ? [] : Db::name('category_product')->where(['product_id'=>$model->id])->column('category_id');

            return $this->fetch('set', [
                'message'=>Session::get('message'),
                'data' => $data,
                // 库存单位列表
                'skus' => SkuModel::order(['sort'=>'asc'])->select(),
                'stock_status_list' => StockStatusModel::order(['sort'=>'asc'])->select(), // 库存状态
                'length_unit_list' => LengthUnitModel::order(['sort'=>'asc'])->select(), // 长度单位
                'weight_unit_list' => WeightUnitModel::order(['sort'=>'asc'])->select(), // 重量单位
                'tax_list' => TaxModel::order(['sort'=>'asc'])->select(), // 重量单位
                'brand_list' => BrandModel::order(['sort'=>'asc'])->select(), // 重量单位
                'category_list' => (new CategoryModel())->getTree(), // 重量单位
                'selected_categories' => $selected_categories, // 已选的分类,
                'type_list' => TypeModel::order(['sort'=>'asc'])->select(),// 类型列表
            ]);
        }

        // post请求方式
        elseif ($request->isPost()) {
            // 获取提交的数据
            $data = $request->post();
            // 数据校验是否通过
            $validate = Loader::validate('ProductSetValidate');// use think\Loader;
            // 批量验证传入的数据
            $result = $validate->batch(true)->check($data);
            if (! $result) {
                // 未通过验证
                // 重定向到add, 通常需要携带错误消息和错误数据
                $this->redirect('set', [], 302, [
                    'message'=>$validate->getError(),
                    'data' => $data,
                ]);
            }

            // 模型填充数据
            $model->data($data);
            // 存储, 可以同时完成insert 和 update
            $model->allowField(['title', 'upc', 'image', 'image_thumb', 'sku_id', 'quantity', 'stock_status_id', 'is_subtract', 'price', 'price_origin', 'minimum', 'is_shipping', 'date_available', 'lenght', 'width', 'height', 'length_unit_id', 'weight', 'weight_unit_id', 'tax_id', 'is_on_sale', 'description', 'sort', 'brand_id', 'type_id', 'meta_title', 'meta_keywords', 'meta_description'])->save();

            # 处理商品与分类的联系
            // 得到当前商品已经存在的对应分类id列表
            $old_ids = Db::name('category_product')->where(['product_id'=>$model->id])->column('category_id');
            $old_ids = $old_ids ?: [];
            // 得到当前所选的id列表
            $ids = input('category_ids/a', []);
            // 确认删除的
            $deleted_ids = array_diff($old_ids, $ids);
            Db::name('category_product')->where(['product_id'=>$model->id, 'category_id'=>['in', $deleted_ids]])->delete();
            // 确认需要插入的
            $insert_ids = array_diff($ids, $old_ids); //[3, 1, 7]
            $rows = array_map(function($category_id) use($model) {
                return [
                    'category_id' => $category_id,
                    'product_id' => $model->id,
                ];
            }, $insert_ids);// [['category_id'=>3, 'product_id'=>12, ], ['category_id'=>1, 'product_id'=>12, ]]
            $categoryProductModel = new CategoryProductModel();
            $categoryProductModel->saveAll($rows);

            # 商品与属性的关系
            // 找到需要插入的, 原来没有的属性
            // 找到需要更新的, 原来有(值可能变化)
            // 找到需要删除的, 原来有但是新数据没有

            // 存在的属性id
            $old_attribute_ids = Db::name('product_attribute')->where(['product_id'=>$model->id])->column('attribute_id');
            $old_attribute_ids = $old_attribute_ids ?: [];
            // 本次提交属性id
            $attribute_ids = [];
            foreach(input('attr/a', []) as $attr) {
                $attribute_ids[] = $attr['attribute_id'];
                // 判断是否存在与旧的数组中
                $productAttributeModel = new ProductAttributeModel();
                if (in_array($attr['attribute_id'], $old_attribute_ids)) {
                    // 存在, 执行更新
                    $productAttributeModel
                        ->save(
                            ['value'=>$attr['value']],
                            [
                                'product_id'=>$model->id,
                                'attribute_id' => $attr['attribute_id'],
                            ]);
                } else {
                    // 不存在, 执行插入
                    $productAttributeModel->save([
                        'product_id'=>$model->id,
                        'attribute_id' => $attr['attribute_id'],
                        'value' => $attr['value'],
                    ]);
                }
            }
            // 需要删除
            $deleted_ids = array_diff($old_attribute_ids, $attribute_ids);
            Db::name('product_attribute')->where([
                'product_id'=>$model->id,
                'attribute_id' => ['in', $deleted_ids]
            ])->delete();


            // 重定向到
            $this->redirect('index');
        }
    }

    /**
     * 索引列表
     * @return mixed
     */
    public function index()
    {

        # 设置过滤条件
        $where = $filter = [];
        // 判断是否传递了过滤条件
        // 设置
        $query = ProductModel::alias('p');

        $query->where($where);

        # 处理排序
        $order = [];
        $field = input('order_field', '');
        $type = input('order_type', 'asc');
        // 存在排序字段
        if ($field !== '') {
            $query->order([$field=>$type]);
            // 分配到模板, 用于确定URL
            $order = ['field'=>'p.' . $field, 'type'=>$type];
        } else {
            $query->order('g.sn');
        }

        # 关联分组
        $query->join('__GROUP__ g', 'p.group_id=g.id', 'LEFT');

        # 获取数据
        $limit = 12;
        $rows = $query
            ->field('p.*, g.sn group_sn')
            ->paginate($limit);

        # 模板展示
        return $this->fetch('index', [
            'rows'=>$rows,
            'filter'=>$filter,
            'order'=>$order,
            'message' => Session::get('message'),
        ]);
    }

    /**
     * 批量处理
     */
    public function multi()
    {
        // 全部勾选的商品id
        $ids = input('selected/a', []);
        switch (input('operate', 'delete')) {
            # 组合操作
            case 'group':
                // 判断参与组合的商品, 是否满足组合需要.
                // 得到全部的type_id和group_id
                $rows = ProductModel::field('type_id, group_id')->where(['id'=>['in', $ids]])->select();
                $type_ids = $group_ids = [];
                foreach($rows as $row) {
                    $type_ids[$row['type_id']] = 1;
                    if ($row['group_id'] != '0') // 0 不属于任何分组
                        $group_ids[$row['group_id']] = 1;
                }
                if (count($type_ids) > 1 || count($group_ids) > 1) {
                    // 不能组合
                    $this->redirect('index', [], '302', ['message'=>'组合商品不属于一个类型或者处于多个组中']);
                }
                // 执行组合:
                if (count($group_ids) == 0) {
                    // 没有任何分组, 新建组, 加入组
                    $group = new GroupModel();
                    $group->sn = md5(date('YmdHis') . mt_rand(100, 999) . mt_rand(100, 999));
                    $group->save();
                    $group_id  = $group->id;
                } else {
                    // 已经存在某个组
                    $group_id = array_keys($group_ids)[0];
                }
                // 组合
                ProductModel::where(['id'=>['in', $ids]])
                    ->update(['group_id'=>$group_id]);
                $this->redirect('index', [], '302', ['message'=>'组合成功']);
                break;
            case 'delete':
            default:
                // 利用模型完成删除即可
                ProductModel::destroy($ids);
                // 重定向
                $this->redirect('index');
        }

    }

    /**
     * 获取类型下属性
     * @param $id 产品id
     */
    public function getAttr($id=null)
    {
        $type_id = input('type_id');

        $attributeModel = new AttributeModel();
        $attributeModel->alias('a');
        if (!is_null($id)) {
            // 存在商品id
            $attributeModel
                ->field('a.id, a.title, pa.value')
                ->join('__PRODUCT_ATTRIBUTE__ pa', 'a.id=pa.attribute_id AND pa.product_id='.$id, 'LEFT');
        } else {
            $attributeModel
                ->field('a.id, a.title')                ;
        }
        $rows = $attributeModel
            ->where(['a.type_id'=>$type_id])
            ->select();

        return [
            'error' => false,
            'data' => $rows,
        ];
    }
}