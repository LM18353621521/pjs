<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------

namespace app\user\controller;

use cmf\controller\AdminBaseController;
use think\Db;

error_reporting(E_ERROR | E_PARSE);

/**
 * 患者中心名称及诊断管理
 * Class MedicalHistoryController
 * @package app\user\controller
 */
class ScaleController extends AdminBaseController
{
    public $inc_type;

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $user_id = input('user_id');
        $inc_type = input('inc_type', 'mds_updrs');
        $this->inc_type = $inc_type;
        $user = Db::name('user')->find($user_id);
        $this->user = $user;
        $this->admin_id = session('ADMIN_ID');
        $type_list = array(
            'mds_updrs' => 'PD统一评分量表',
            'sc_en' => 'Schwab和England残疾量表',
            'nmss' => '非运动症状问卷调查',
            'mmse' => '简明智力状态量表',
            'moca' => '蒙特利尔认知评估（MoCA）量表',
            'hamd' => 'Hamilton抑郁量表(HAMD)',
            'hama' => 'Hamilton焦虑量表(HAMA)',
            'maes' => 'PD改良淡漠量表（MAES）',
            'psqi' => '匹兹堡睡眠质量指数(PSQI)量表',
            'ess' => '爱泼沃斯思睡量表(ESS)',
            'fai' => '疲劳评定量表 ',
            'npi' => '神经精神症状问卷（NPI）',
            'pdql' => 'PD生活质量问卷(PDQL-39)',
            'adl' => '日常生活能力（ADL）量表',
            'dlb' => '路易体痴呆专用病史及症状',
        );
        $this->assign('type_list', $type_list);
        $this->assign('inc_type', $inc_type);


        $this->assign('user', $user);
    }

    /**
     * 数据列表
     * @return mixed
     */
    public function index()
    {
        $request = input('request.');
        if (!empty($request['user_nickname'])) {
            $where['user_nickname'] = ['like', "%" . $request['user_nickname'] . "%"];
        }
        if (!empty($request['title'])) {
            $where['title'] = array('like', '%' . $request['title'] . '%');
        }
        if (!empty($request['start_time']) && !empty($request['end_time'])) {
            $start_time = strtotime($request['start_time']);
            $end_time = strtotime($request['end_time']) + 86400;
            $where['create_time'] = array('between', [$start_time, $end_time]);
        }
        $where['user_id'] = input('user_id');

        $keywordComplex = [];
        $usersQuery = Db::name('scale_' . $this->inc_type);

        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("create_time DESC")->paginate(15);
        // 获取分页显示
        $list->appends($request);
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch($this->inc_type . "_index");
    }

    /**
     * 添加数据
     * @adminMenu(
     *     'name'   => '添加文章',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 添加/编辑数据提交
     * @adminMenu(
     *     'name'   => '添加文章提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        $model = Db::name('scale_' . $this->inc_type);
        if ($this->request->isPost()) {
            $data = $this->request->param();
            unset($data['inc_type']);

            foreach ($data as &$val) {
                if (is_array($val)) {
                    $val = serialize($val);
                }
            }
            if (empty($data['id'])) {
                $data['admin_id'] = $this->admin_id;
                $data['create_time'] = time();
                $res = $model->insert($data);
                adminLog("添加中心名称及诊断(ID:$res)");
                $this->success('添加成功!', url('Scale/index', ['user_id' => $data['user_id'], 'inc_type' => $this->inc_type]));
            } else {
                $data['update_time'] = time();
                $res = $model->where(array('id' => $data['id']))->update($data);
                adminLog("编辑中心名称及诊断(ID:" . $data['id'] . ")");
                $this->success('编辑成功!', url('Scale/index', ['user_id' => $data['user_id'], 'inc_type' => $this->inc_type]));
            }
        }

    }

    /**
     * @return mixed编辑数据
     */
    public function edit()
    {
        $model = Db::name('scale_' . $this->inc_type);
        $id = $this->request->param('id', 0, 'intval');
        $data = $model->find($id);
        $inc_type = $this->inc_type;
        if ($data) {
            if ($inc_type == "mds_updrs") {
                $data['part1'] = unserialize($data['part1']);
                $data['part2'] = unserialize($data['part2']);
                $data['part3'] = unserialize($data['part3']);
                $data['part4'] = unserialize($data['part4']);
            } elseif ($inc_type == "sc_en") {
                $data['items'] = unserialize($data['items']);
            } elseif ($inc_type == "nmss") {
                $data['items'] = unserialize($data['items']);
            } elseif ($inc_type == "mmse") {
                $data['items'] = unserialize($data['items']);
            } elseif ($inc_type == "hamd") {
                $data['items'] = unserialize($data['items']);
            } elseif ($inc_type == "hama") {
                $data['items'] = unserialize($data['items']);
            } elseif ($inc_type == "maes") {
                $data['items'] = unserialize($data['items']);
            } elseif ($inc_type == "psqi") {
                $data['items'] = unserialize($data['items']);
            } elseif ($inc_type == "ess") {
                $data['items'] = unserialize($data['items']);
            }elseif ($inc_type == "fai") {
                $data['items'] = unserialize($data['items']);
            }elseif ($inc_type == "npi") {
                $data['items'] = unserialize($data['items']);
            }elseif ($inc_type == "pdql") {
                $data['items'] = unserialize($data['items']);
            }
            elseif ($inc_type == "adl") {
                $data['items'] = unserialize($data['items']);
            }
            elseif ($inc_type == "dlb") {
                $data['items'] = unserialize($data['items']);
            }
            elseif ($inc_type == "moca") {
                $data['items'] = unserialize($data['items']);
            }
        }

        if ($inc_type == "mds_updrs") {
        } elseif ($inc_type == "sc_en") {
            $items_list = array(
                '100' => array('name' => '完全自理，无动作缓慢、动作困难或动作障碍，无任何困难的感觉；', 'unit' => ''),
                '90' => array('name' => '完全自理，轻微动作缓慢、动作困难或动作障碍，或许要花比正常多两倍的时间，感觉有些困难；', 'unit' => ''),
                '80' => array('name' => '大多数情况下完全自理，要花比正常多两倍的时间，感觉有些困难和迟缓；', 'unit' => ''),
                '70' => array('name' => '不能完全自理，处理日常活动较吃力，要花比正常多3－4倍的 时间； ', 'unit' => ''),
                '60' => array('name' => '一定的对人依赖性，可作大部分日常活动，但缓慢而吃力，易出错，有些事作不了；', 'unit' => ''),
                '50' => array('name' => '依赖别人，做任何事都吃力；', 'unit' => ''),
                '40' => array('name' => '不能自理，多数活动需别人帮助才能完成；', 'unit' => ''),
                '30' => array('name' => '绝大多数活动需别人帮助才能完成；', 'unit' => ''),
                '20' => array('name' => '有些事情能作一点，但自己不能完成任何日常活动，严重病残；', 'unit' => ''),
                '10' => array('name' => '完全不能自理，完全病残；', 'unit' => ''),
                '0' => array('name' => '植物神经功能如吞咽及大小便功能障碍，长期卧床', 'unit' => ''),
            );
            $this->assign('items_list', $items_list);
        } elseif ($inc_type == "nmss") {
            $items_list = array(
                '1' => array('name' => '白天流涎', 'unit' => ''),
                '2' => array('name' => '味觉或嗅觉减退或消失', 'unit' => ''),
                '3' => array('name' => '吞咽困难或饮水呛咳或有过窒息', 'unit' => ''),
                '4' => array('name' => '感到身体不适（眩晕）', 'unit' => ''),
                '5' => array('name' => '便秘（大便1周少于3次），或需要用力排便', 'unit' => ''),
                '6' => array('name' => '大便失禁', 'unit' => ''),
                '7' => array('name' => '如厕后，感到肠道未完全排空', 'unit' => ''),
                '8' => array('name' => '感到小便难以控制，以至于慌忙如厕', 'unit' => ''),
                '9' => array('name' => '夜里常要起来小便', 'unit' => ''),
                '10' => array('name' => '有不明原因的疼痛（并非关节炎等已知的原因引起）', 'unit' => ''),
                '11' => array('name' => '有不明原因的体重改变（并非节食引起）', 'unit' => ''),
                '12' => array('name' => '对近期发生的事情记不住或忘记做事情', 'unit' => ''),
                '13' => array('name' => '对身边发生的事失去兴趣或对做事情无兴趣', 'unit' => ''),
                '14' => array('name' => '看到或听到一些事情，但你知道或是别人告诉你这实际上并不存在（幻视或幻听）', 'unit' => ''),
                '15' => array('name' => '难以集中注意力或专注地做事。', 'unit' => ''),
                '16' => array('name' => '感到悲伤、情绪低落或忧郁', 'unit' => ''),
                '17' => array('name' => '感到焦虑、害怕或恐惧', 'unit' => ''),
                '18' => array('name' => '对性失去兴趣或对性非常有兴趣', 'unit' => ''),
                '19' => array('name' => '发现即使努力，也有性生活障碍', 'unit' => ''),
                '20' => array('name' => '从卧位或坐位站起时，感到头晕眼花、眩晕或无力', 'unit' => ''),
                '21' => array('name' => '跌倒', 'unit' => ''),
                '22' => array('name' => '在活动（如工作、开车或吃饭等）时感到困倦', 'unit' => ''),
                '23' => array('name' => '感到难以入睡或失眠', 'unit' => ''),
                '24' => array('name' => '有非常生动的或可怕的梦境', 'unit' => ''),
                '25' => array('name' => '在睡眠时说话或活动，就像在真实生活中一样', 'unit' => ''),
                '26' => array('name' => '晚上或休息时感到腿部不适，并感到需要活动下肢', 'unit' => ''),
                '27' => array('name' => '下肢浮肿', 'unit' => ''),
                '28' => array('name' => '多汗', 'unit' => ''),
                '29' => array('name' => '复视', 'unit' => ''),
                '30' => array('name' => '相信一些事情发生了，但别人认为这些事情不存在（妄想）。', 'unit' => ''),
            );
            $this->assign('items_list', $items_list);
        } elseif ($inc_type == "mmse") {
            $items_list = array(
                '1' => array('name' => '今年的年份?  ', 'unit' => ''),
                '2' => array('name' => '现在是什么季节? ', 'unit' => ''),
                '3' => array('name' => '今天是几号? ', 'unit' => ''),
                '4' => array('name' => '今天是星期几? ', 'unit' => ''),
                '5' => array('name' => '现在是几月份？', 'unit' => ''),
                '6' => array('name' => '你能告诉我现在我们在哪里? 例如：现在我们在哪个省，市?', 'unit' => ''),
                '7' => array('name' => '你住在什么区(县)?  ', 'unit' => ''),
                '8' => array('name' => '你住在什么街道?  ', 'unit' => ''),
                '9' => array('name' => '我们现在是第几楼?   ', 'unit' => ''),
                '10' => array('name' => '这儿是什么地方?  ', 'unit' => ''),
                '11' => array('name' => '皮球 ', 'unit' => ''),
                '12' => array('name' => '国旗', 'unit' => ''),
                '13' => array('name' => '树木', 'unit' => ''),
                '14' => array('name' => "现在请你从100减去7，然后从所得的数目再减去7，如此一直计算下去，把每一个答案都告诉我，直到我说“停”为止<br>93", 'unit' => ''),
                '15' => array('name' => '93-7=86', 'unit' => ''),
                '16' => array('name' => '86-6=79', 'unit' => ''),
                '17' => array('name' => '79-7=72', 'unit' => ''),
                '18' => array('name' => '72-7=65', 'unit' => ''),
                '19' => array('name' => '现在请你告诉我，刚才我要你记住的三样东西是什么?<br>第一样：皮球', 'unit' => ''),
                '20' => array('name' => '国旗', 'unit' => ''),
                '21' => array('name' => '树木', 'unit' => ''),
                '22' => array('name' => '请问这是什么?<br>手表', 'unit' => ''),
                '23' => array('name' => '请问这是什么?<br>铅笔', 'unit' => ''),
                '24' => array('name' => '重复：四十四只石狮子” ', 'unit' => ''),
                '25' => array('name' => '请闭上您的眼睛', 'unit' => ''),
                '26' => array('name' => '请用右手拿这张纸 ', 'unit' => ''),
                '27' => array('name' => '把纸对折 ', 'unit' => ''),
                '28' => array('name' => '放在大腿上  ', 'unit' => ''),
                '29' => array('name' => '请你说一句完整的，有意义的句子(句子必须有主语，动词)记下所叙述句子的全文 ', 'unit' => ''),
                '30' => array('name' => '是一张图，请你在同一张纸上照样把它画出来。(对：两个五边形的图案，交叉处形成个小四边形)', 'unit' => ''),
            );
            $this->assign('items_list', $items_list);
        } elseif ($inc_type == "hamd") {
            $items_list = array(
                '1' => array('name' => '抑郁情绪:  ', 'unit' => '5'),
                '2' => array('name' => '有罪感:', 'unit' => '5'),
                '3' => array('name' => '自杀: ', 'unit' => '5'),
                '4' => array('name' => '入睡困难初段失眠:', 'unit' => '3'),
                '5' => array('name' => '睡眠不深中段失眠:', 'unit' => '3'),
                '6' => array('name' => '早醒末段失眠:', 'unit' => '3'),
                '7' => array('name' => '工作和兴趣-旁人的评价:', 'unit' => '5'),
                '8' => array('name' => '阻滞(指思维和言语缓慢，注意力难以集中，主动性减退):  ', 'unit' => '5'),
                '9' => array('name' => '激越－最好是专业人士观察:', 'unit' => '5'),
                '10' => array('name' => '精神性焦虑:', 'unit' => '5'),
                '11' => array('name' => '躯体性焦虑－最好是专业人士观察指焦虑的生理症状，包括:口干、腹胀、腹泻、打呃、腹绞痛、心悸、头痛、过度换气和叹气，以及尿频和出汗: ', 'unit' => '5'),
                '12' => array('name' => '胃肠道症状:', 'unit' => '3'),
                '13' => array('name' => '全身症状:四肢，背部或颈部沉重感，背痛、头痛、肌肉疼痛，全身乏力或疲倦:', 'unit' => '5'),
                '14' => array('name' => "性症状指性欲减退，月经紊乱等:", 'unit' => '4'),
                '15' => array('name' => '疑病:', 'unit' => '5'),
                '16' => array('name' => '体重减轻:按病史评定:', 'unit' => '3'),
                '17' => array('name' => '自知力:', 'unit' => '3'),
                '18' => array('name' => '日夜变化如果症状在早晨或傍晚加重，先指出是哪一种，然后按其变化程度评分早上变化评早上，晚上变化评晚上:', 'unit' => '5'),
                '19' => array('name' => '人格解体或现实解体指非真实感或虚无妄想:', 'unit' => '5'),
                '20' => array('name' => '偏执症状:', 'unit' => '5'),
                '21' => array('name' => '强迫症状指强迫思维和强迫行为: ', 'unit' => '3'),
                '22' => array('name' => '能力减退感－旁人的评价: ', 'unit' => '5'),
                '23' => array('name' => '绝望感: ', 'unit' => '5'),
                '24' => array('name' => '自卑感: ', 'unit' => '5'),
            );
            $this->assign('items_list', $items_list);
        } elseif ($inc_type == "hama") {
            $items_list = array(
                '1' => array('name' => '焦虑心境', 'unit' => ''),
                '2' => array('name' => '紧张 ', 'unit' => ''),
                '3' => array('name' => '害怕', 'unit' => ''),
                '4' => array('name' => '失眠', 'unit' => ''),
                '5' => array('name' => '认知功能', 'unit' => ''),
                '6' => array('name' => '抑郁心境', 'unit' => ''),
                '7' => array('name' => '躯体性焦虑：肌肉系统', 'unit' => ''),
                '8' => array('name' => '躯体性焦虑：感觉系统', 'unit' => ''),
                '9' => array('name' => '心血管系统症状 ', 'unit' => ''),
                '10' => array('name' => '呼吸系统症状', 'unit' => ''),
                '11' => array('name' => '胃肠道症状 ', 'unit' => ''),
                '12' => array('name' => '生殖泌尿系统症状', 'unit' => ''),
                '13' => array('name' => '植物神经系统症状', 'unit' => ''),
                '14' => array('name' => "会谈时行为表现", 'unit' => ''),
            );
            $this->assign('items_list', $items_list);
        } elseif ($inc_type == "maes") {
            $items_list = array(
                '1' => array('name' => '您对学习新事物有兴趣吗？', 'unit' => ''),
                '2' => array('name' => '您有什么感兴趣的事情吗？ ', 'unit' => ''),
                '3' => array('name' => '您关心自己的身体状况吗？', 'unit' => ''),
                '4' => array('name' => '您付出很大的努力去做事情吗？', 'unit' => ''),
                '5' => array('name' => '您一直期待着做点什么事吗？', 'unit' => ''),
                '6' => array('name' => '您对未来有计划和目标吗？', 'unit' => ''),
                '7' => array('name' => '您做事情有积极性吗？', 'unit' => ''),
                '8' => array('name' => '您对日常生活有动力吗？', 'unit' => ''),
                '9' => array('name' => '您需要别人告诉您每天要干什么吗？ ', 'unit' => ''),
                '10' => array('name' => '您对事情都没有兴趣吗？', 'unit' => ''),
                '11' => array('name' => '您对很多事情都不关心吗？ ', 'unit' => ''),
                '12' => array('name' => '您需要有一个动力去推动您做事情吗？', 'unit' => ''),
                '13' => array('name' => '您是否有既不高兴也不悲伤，无所谓的感觉？', 'unit' => ''),
                '14' => array('name' => "您认为自己有淡漠的表现吗？", 'unit' => ''),
            );
            $this->assign('items_list', $items_list);
        } elseif ($inc_type == "psqi") {
            $items_list = array(
                'a' => array('name' => '入睡困难（30分钟内不能入睡）', 'unit' => ''),
                'b' => array('name' => '夜间易醒或早醒  ', 'unit' => ''),
                'c' => array('name' => '夜间去厕所 ', 'unit' => ''),
                'd' => array('name' => '呼吸不畅 ', 'unit' => ''),
                'e' => array('name' => '咳嗽或鼾声高 ', 'unit' => ''),
                'f' => array('name' => '感觉冷', 'unit' => ''),
                'g' => array('name' => '感觉热 ', 'unit' => ''),
                'h' => array('name' => '做噩梦', 'unit' => ''),
                'i' => array('name' => '疼痛不适 ', 'unit' => ''),
                'j' => array('name' => '其他影响睡眠的事情', 'unit' => ''),
            );
            $this->assign('items_list', $items_list);
        } elseif ($inc_type == "ess") {
            $items_list = array(
                '1' => array('name' => '坐着阅读书刊', 'unit' => ''),
                '2' => array('name' => '看电视', 'unit' => ''),
                '3' => array('name' => '在公共场合坐着不动（如剧院或开会） ', 'unit' => ''),
                '4' => array('name' => '乘坐汽车超过1小时，中间不休息 ', 'unit' => ''),
                '5' => array('name' => '环境许可，在下午躺下休息 ', 'unit' => ''),
                '6' => array('name' => '坐下与人谈话', 'unit' => ''),
                '7' => array('name' => '午餐未喝酒，餐后安静地坐着 ', 'unit' => ''),
                '8' => array('name' => '遇堵车时停车数分钟以上', 'unit' => ''),
            );
            $this->assign('items_list', $items_list);
        } elseif ($inc_type == "fai") {
            $items_list = array(
                '1' => array('name' => '你有过被疲劳困扰的经历吗？', 'unit' => ''),
                '2' => array('name' => '你是否需要更多休息。', 'unit' => ''),
                '3' => array('name' => '你感觉到犯困或昏昏欲睡吗？ ', 'unit' => ''),
                '4' => array('name' => '你在着手做事时是否感到费力。 ', 'unit' => ''),
                '5' => array('name' => '你在着手做事时并不感到费力，但当你继续进行时是否感到力不从心？ ', 'unit' => ''),
                '6' => array('name' => '你感到体力不够吗？', 'unit' => ''),
                '7' => array('name' => '你感到你的肌肉力量比以前减小了吗？ ', 'unit' => ''),
                '8' => array('name' => '你感到虚弱吗？', 'unit' => ''),
                '9' => array('name' => '你集中注意力有困难吗？', 'unit' => ''),
                '10' => array('name' => '你在思考问题是头脑像往常一样清晰、敏捷吗？', 'unit' => ''),
                '11' => array('name' => '你在讲话时出现口头不利落吗？', 'unit' => ''),
                '12' => array('name' => '讲话时，你发现找到一个合适的字眼很困难吗？', 'unit' => ''),
                '13' => array('name' => '你现在的记忆力像往常一样吗？', 'unit' => ''),
                '14' => array('name' => '你还喜欢做过去习惯做的事情吗？', 'unit' => ''),
            );
            $this->assign('items_list', $items_list);
        }
        elseif ($inc_type == "npi") {
            $items_list = array(
                '1' => array('name' => '妄想：（错误的观念如：认为别人偷他/她的东西？怀疑有人害他？）', 'unit' => ''),
                '2' => array('name' => '幻觉：（视幻觉或听幻觉？看到或听到不存在的东西或声音？和实际不存在的人说话？）', 'unit' => ''),
                '3' => array('name' => '激越/攻击性：（拒绝别人的帮助？难以驾驭？固执？向别人大喊大叫？打骂别人？） ', 'unit' => ''),
                '4' => array('name' => '抑郁/恶劣心境：（说或表现出伤心或情绪低落？哭泣？） ', 'unit' => ''),
                '5' => array('name' => '焦虑：（与照料者分开后不安？精神紧张的表现如呼吸急促、叹气、不能放松或感觉紧张？对将来的事情担心？） ', 'unit' => ''),
                '6' => array('name' => '欣快：（过于高兴、感觉过于良好？对别人并不觉得有趣的事情感到幽默并开怀大笑？与情景不符合的欢乐？）', 'unit' => ''),
                '7' => array('name' => '情感淡漠：（对以前感兴趣的活动失去兴趣？对别人的活动和计划漠不关心？自发活动比以前减少？） ', 'unit' => ''),
                '8' => array('name' => '脱抑制：（行为突兀，如与陌生人讲话，自来熟？说话不顾及别人的感受？说一些粗话或谈论性？而以前他/她不会说这些）', 'unit' => ''),
                '9' => array('name' => '易激惹/情绪不稳：（不耐烦或疯狂的举动？对延误无法忍受？对计划中的活动不能耐心等待？突然爆发？）', 'unit' => ''),
                '10' => array('name' => '异常运动行为：（反复进行无意义的活动，如围着房屋转圈、摆弄纽扣、用绳子包扎捆绑等？无目的的活动、多动？）', 'unit' => ''),
                '11' => array('name' => '睡眠/夜间行为：（晚上把别人弄醒？早晨很早起床？白天频繁打盹？）', 'unit' => ''),
                '12' => array('name' => '食欲和进食障碍：（体重增加？体重减轻？喜欢食物的口味发生变化？）', 'unit' => ''),
            );
            $this->assign('items_list', $items_list);
        }
        elseif ($inc_type == "npi") {
            $items_list = array(
                '1' => array('name' => '妄想：（错误的观念如：认为别人偷他/她的东西？怀疑有人害他？）', 'unit' => ''),
                '2' => array('name' => '幻觉：（视幻觉或听幻觉？看到或听到不存在的东西或声音？和实际不存在的人说话？）', 'unit' => ''),
                '3' => array('name' => '激越/攻击性：（拒绝别人的帮助？难以驾驭？固执？向别人大喊大叫？打骂别人？） ', 'unit' => ''),
                '4' => array('name' => '抑郁/恶劣心境：（说或表现出伤心或情绪低落？哭泣？） ', 'unit' => ''),
                '5' => array('name' => '焦虑：（与照料者分开后不安？精神紧张的表现如呼吸急促、叹气、不能放松或感觉紧张？对将来的事情担心？） ', 'unit' => ''),
                '6' => array('name' => '欣快：（过于高兴、感觉过于良好？对别人并不觉得有趣的事情感到幽默并开怀大笑？与情景不符合的欢乐？）', 'unit' => ''),
                '7' => array('name' => '情感淡漠：（对以前感兴趣的活动失去兴趣？对别人的活动和计划漠不关心？自发活动比以前减少？） ', 'unit' => ''),
                '8' => array('name' => '脱抑制：（行为突兀，如与陌生人讲话，自来熟？说话不顾及别人的感受？说一些粗话或谈论性？而以前他/她不会说这些）', 'unit' => ''),
                '9' => array('name' => '易激惹/情绪不稳：（不耐烦或疯狂的举动？对延误无法忍受？对计划中的活动不能耐心等待？突然爆发？）', 'unit' => ''),
                '10' => array('name' => '异常运动行为：（反复进行无意义的活动，如围着房屋转圈、摆弄纽扣、用绳子包扎捆绑等？无目的的活动、多动？）', 'unit' => ''),
                '11' => array('name' => '睡眠/夜间行为：（晚上把别人弄醒？早晨很早起床？白天频繁打盹？）', 'unit' => ''),
                '12' => array('name' => '食欲和进食障碍：（体重增加？体重减轻？喜欢食物的口味发生变化？）', 'unit' => ''),
            );
            $this->assign('items_list', $items_list);
        }
        elseif ($inc_type == "pdql") {
            $items_list = array(
                '1' => array('name' => '外出需要陪同', 'unit' => ''),
                '2' => array('name' => '普遍感觉不适', 'unit' => ''),
                '3' => array('name' => '你再也不能对爱好产生兴趣', 'unit' => ''),
                '4' => array('name' => '变得紧张', 'unit' => ''),
                '5' => array('name' => '由于自己身体限制，感觉不安全  ', 'unit' => ''),
                '6' => array('name' => '手震颤', 'unit' => ''),
                '7' => array('name' => '感觉累或没有能量 ', 'unit' => ''),
                '8' => array('name' => '做运动或休闲活动的时候觉得困难', 'unit' => ''),
                '9' => array('name' => '笨拙', 'unit' => ''),
                '10' => array('name' => '因为疾病感到尴尬 ', 'unit' => ''),
                '11' => array('name' => '拖步', 'unit' => ''),
                '12' => array('name' => '因为疾病不得不推迟或取消社会活动', 'unit' => ''),
                '13' => array('name' => '极度疲惫', 'unit' => ''),
                '14' => array('name' => '走路时拐弯困难', 'unit' => ''),
                '15' => array('name' => '害怕疾病进展', 'unit' => ''),
                '16' => array('name' => '书写困难', 'unit' => ''),
                '17' => array('name' => '出去度假的时间比得病之前少', 'unit' => ''),
                '18' => array('name' => '在其他人旁边感觉自己不安全', 'unit' => ''),
                '19' => array('name' => '很难找一个能休息好的晚上', 'unit' => ''),
                '20' => array('name' => '“开/关”现象', 'unit' => ''),
                '21' => array('name' => '接受自己的病很困难', 'unit' => ''),
                '22' => array('name' => '沟通困难', 'unit' => ''),
                '23' => array('name' => '在公众之前签名困难', 'unit' => ''),
                '24' => array('name' => '行走困难', 'unit' => ''),
                '25' => array('name' => '流涎', 'unit' => ''),
                '26' => array('name' => '消沉或沮丧', 'unit' => ''),
                '27' => array('name' => '坐立困难（长期间）', 'unit' => ''),
                '28' => array('name' => '尿频和/或排尿时弄湿自己', 'unit' => ''),
                '29' => array('name' => '搬运东西困难', 'unit' => ''),
                '30' => array('name' => '痛性痉挛或抽搐', 'unit' => ''),
                '31' => array('name' => '注意力集中困难', 'unit' => ''),
                '32' => array('name' => '从椅子上坐起来困难 ', 'unit' => ''),
                '33' => array('name' => '便秘', 'unit' => ''),
                '34' => array('name' => '记忆困难', 'unit' => ''),
                '35' => array('name' => '在床上翻身困难', 'unit' => ''),
                '36' => array('name' => '你的病影响你的性生活', 'unit' => ''),
                '37' => array('name' => '不耐受冷或热', 'unit' => ''),
                '38' => array('name' => '夜间噩梦或出现幻觉', 'unit' => ''),
                '39' => array('name' => '系扣子或系鞋带有困难', 'unit' => ''),
            );
            $this->assign('items_list', $items_list);
        }
        elseif ($inc_type == "adl") {
            $items_list = array(
                '1' => array('name' => '自己搭公共汽车', 'unit' => ''),
                '2' => array('name' => '到家附近的地方去走走', 'unit' => ''),
                '3' => array('name' => '自己做饭（包括生火）', 'unit' => ''),
                '4' => array('name' => '做家务', 'unit' => ''),
                '5' => array('name' => '吃药 ', 'unit' => ''),
                '6' => array('name' => '吃饭', 'unit' => ''),
                '7' => array('name' => '穿脱衣服 ', 'unit' => ''),
                '8' => array('name' => '梳头、刷牙', 'unit' => ''),
                '9' => array('name' => '洗自己的衣服', 'unit' => ''),
                '10' => array('name' => '在平坦的室内走动 ', 'unit' => ''),
                '11' => array('name' => '上下楼梯', 'unit' => ''),
                '12' => array('name' => '上下床、坐下或站起', 'unit' => ''),
                '13' => array('name' => '提水煮饭或洗澡', 'unit' => ''),
                '14' => array('name' => '洗澡（水已别人放好）', 'unit' => ''),
                '15' => array('name' => '剪脚趾甲', 'unit' => ''),
                '16' => array('name' => '逛街，购物', 'unit' => ''),
                '17' => array('name' => '定时去厕所', 'unit' => ''),
                '18' => array('name' => '打电话', 'unit' => ''),
                '19' => array('name' => '处理自己的钱财', 'unit' => ''),
                '20' => array('name' => '独自在家', 'unit' => ''),
            );
            $this->assign('items_list', $items_list);
        }
        elseif ($inc_type == "dlb") {
            $items_list = array(
                '1' => array('name' => '波动性认知障碍出现的时间', 'unit' => ''),
                '2' => array('name' => '到家附近的地方去走走', 'unit' => ''),
                '3' => array('name' => '自己做饭（包括生火）', 'unit' => ''),
                '4' => array('name' => '做家务', 'unit' => ''),
                '5' => array('name' => '吃药 ', 'unit' => ''),
                '6' => array('name' => '吃饭', 'unit' => ''),
                '7' => array('name' => '穿脱衣服 ', 'unit' => ''),
                '8' => array('name' => '梳头、刷牙', 'unit' => ''),
                '9' => array('name' => '洗自己的衣服', 'unit' => ''),
                '10' => array('name' => '在平坦的室内走动 ', 'unit' => ''),
                '11' => array('name' => '上下楼梯', 'unit' => ''),
                '12' => array('name' => '上下床、坐下或站起', 'unit' => ''),
                '13' => array('name' => '提水煮饭或洗澡', 'unit' => ''),
                '14' => array('name' => '洗澡（水已别人放好）', 'unit' => ''),
                '15' => array('name' => '剪脚趾甲', 'unit' => ''),
                '16' => array('name' => '逛街，购物', 'unit' => ''),
                '17' => array('name' => '定时去厕所', 'unit' => ''),
                '18' => array('name' => '打电话', 'unit' => ''),
                '19' => array('name' => '处理自己的钱财', 'unit' => ''),
                '20' => array('name' => '独自在家', 'unit' => ''),
            );
            $this->assign('items_list', $items_list);
        }


        $this->assign('data', $data);
        return $this->fetch($this->inc_type . "_edit");
    }

    /**
     * 删除数据
     * @throws \think\Exception
     */
    public function delete()
    {
        $model = Db::name('scale_' . $this->inc_type);
        $param = $this->request->param();
        if (isset($param['id'])) {
            $id = $this->request->param('id', 0, 'intval');
            $result = $model->where(['id' => $id])->find();
            $model->where(['id' => $id])->delete();
            adminLog("删除中心名称及诊断(ID:" . $id . ")");
            $this->success("删除成功！", '');
        }
        if (isset($param['ids'])) {
            $ids = $this->request->param('ids/a');
            $result = $model->where(['id' => ['in', $ids]])->delete();
            adminLog("删除中心名称及诊断(ID:" . implode(",", $ids) . ")");
            if ($result) {
                $this->success("删除成功！", '');
            }
        }
    }
}
