{{ block_begin('head') }}
{{ theme_js('/m/js/resize.js') }}
{{ theme_css('/m/activities/css/karaoke_master.css') }}
{{ block_end() }}
<div class="vueBox details" id="app">
    <div class="rules_title">
        <span>比赛规则</span>
    </div>
    <div class="rules_box">
        <ul>
            <li v-for="rule in rules">
                <div class="rules_tit" v-text="rule.title" v-if="rule.title"></div>
                <p class="rules_txt">
                    <span class="rules_tips" v-text="rule.tips" v-if="rule.tips"></span>
                    <span v-text="rule.text"></span>
                </p>
            </li>
        </ul>
    </div>

    <div class="rules_title">
        <span>评分标准</span>
    </div>
    <div class="standards_box">
        <ul>
            <li v-for="item in standards">
                <div class="standards_tit" v-text="item.title" v-if="item.title"></div>
                <div class="standards_list" v-if="item.list">
                    <div class="standards_txt" v-for="text in item.list">
                        <span class="line_left"></span>
                        <p v-text="text"></p>
                    </div>
                </div>
                <div class="standards_notice" v-if="item.notice">
                    <span class="standards_tips">说明：</span>
                    <span v-text="item.notice"></span>
                </div>
                <div class="standards_tips" v-if="item.uncompleted" v-text="item.uncompleted">

                </div>

            </li>
        </ul>
    </div>
</div>
<script>
    var opts = {
        data: {
            rules: [
                {
                    title: '第一轮海选：',
                    text: '每位选手表演2分钟，表演结束后由5位评委点评（色眼为通过，流汗为淘汰）本轮选出优秀歌手进入下一轮',
                },
                {
                    title: '第二轮：',
                    text: '24人由第一轮通过海选的歌手依次上麦表演，表演时长为2分钟。本轮比赛挑选出优秀的24名歌手进入下一轮次（若不足24人时以当时晋级人数为准）',
                },
                {
                    title: '第三轮：',
                    text: '24进十：本轮会进行评委点评并打分，上一轮晋级的24名歌手依次上麦表演，表演时长为3分钟。本轮比赛挑选出优秀的十名歌手进入半决赛。',
                },
                {
                    title: '第四轮半决赛：',
                    text: '晋级半决赛的十名歌手依次上麦表演，五名评委打分（半决赛分为两轮，第一轮随机抽取出场顺序，第二轮由第一轮的比分排名决定出场顺序，分数较低先出场）两轮分相加前四名进入总决赛。',
                },
                {
                    title: '第五轮总决赛：',
                    text: '四名歌手两两PK（各选两首PK）本轮由评委和随机抽取三位观众投票，获得评委和观众票数最多的两位歌手PK决出冠军，另外两位歌手PK决出三四名。',
                },
                {
                    title: '',
                    tips: '注意：',
                    text: '本次比赛参赛歌曲要积极向上，不得涉黄，涉政，曲风不限，（若发现假唱的一律取消参赛资格），更多未尽事宜比赛主委会第一时间通知。',
                }
            ],

            standards: [
                {
                    title: '1、本次大赛为十分制，去掉一个最高分，去掉一个最低分，所得的平均分就是该选手最后的得分。',

                },
                {
                    title: '2、评分内容包括4个项目：歌曲内容、音色音质、演唱技巧、综合素质。 ',
                    list: [
                        '歌曲内容满分为1分，要求参赛曲目内容健康，积极向上，无不健康画面。',
                        '音色音质满分为4分，要求发音清楚，音色清晰而有质演唱技巧满分为3分，要求整首歌曲的演唱富有感情，音乐感。 ',
                        '节奏感强，歌曲演唱完整。',
                        '综合素质满分为2分，要求选手从所备题中选出一道，30秒内完成。'
                    ],
                    notice: '若遇到歌手得分相同的情况，则增加一场加时赛，歌手可选择备选歌曲中的一首参赛。如果参赛选手未看歌词则酌情予以加分，范围为5-10分。'
                },
                {
                    title: '3、所有参赛选手必须在比赛前30分钟内到达比赛现场并签到。 　　'

                },
                {
                    title: '4、凡迟到、请假或演唱不完整者一律按弃权处理。'

                },
                {
                    uncompleted: '更多未尽事宜请咨询比赛组委… '
                }
            ]

        },
        created: function () {

        },
        methods: {}
    };
    vm = XVue(opts);
</script>
