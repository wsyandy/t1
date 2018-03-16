{{ block_begin('head') }}
{{ theme_css('/m/css/union_main','/m/css/union_index') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak>
    <div class="family_box">
        <div class="family_establish" v-for="(item,index) in family" @click.stop="enterUnion(index,item.url)">
            <img class="family_bg" :src="item.bg" alt="">
            <div class="family_left">
                <div class="family_info">
                    <img class="family-ico" :src="item.ico" alt="">
                    <span class="family_name">${ item.name }</span>
                </div>
                <div class="family_slogan">
                    ${ item.slogan }
                </div>
            </div>
            <img class="family-arrow" :src="arrow_right" alt="">
        </div>

        <div class="family_introduce">
            <div class="family_introduce_title">【 家族说明 】</div>
            <ul>
                <li v-for="(item,i) in family_introduce">
                    ${ i+1 }. ${ item }
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    var opts = {
        data: {
            avatar: "",
            hasFamily: false, /*判断是否有家族*/
            union: {{ union }},
            sid: '{{ sid }}',
            code: '{{ code }}',
            slogan_other: "看看其他好玩的家族",
            arrow_right: "/m/images/ico-arrow-right.png",
            family: [
                {
                    url: "/m/unions/add_union&sid=" + '{{ sid }}' + "&code=" + '{{ code }}',
                    bg: "/m/images/bg-family.png",
                    ico: "/m/images/ico-family.png",
                    name: "创建家族",
                    slogan: "我要做会长，召唤伙伴一起玩"
                },
                {
                    url: "/m/unions/recommend&sid=" + '{{ sid }}' + "&code=" + '{{ code }}',
                    bg: "/m/images/bg-heart.png",
                    ico: "/m/images/ico-heart.png",
                    name: "推荐家族",
                    slogan: "寻找与你兴趣相同的那群人"
                }
            ],
            family_introduce: [
                "Hi语音的用户可以自由选择，加入家族、创建家族或退出家族。",
                "家族徽章可以享有一定特权，例如推荐用户上热门等。",
                "每个用户只能加入一个家族，不能重复加入。",
                "会长可设置新成员加入方式，所有人都可以加入或需要申请才能加入。  ",
                "上热门申请通过后，在申请时间内，用户不开房间，家族会长和该用户会受到一定处罚哦！"
            ]
        },
        created: function () {
            if (this.union) {
                console.log(this.union);
                this.family[0].url = "/m/unions/my_union&sid=" + '{{ sid }}' + "&code=" + '{{ code }}' + '&union_id=' + this.union.id;
                this.family[0].ico = "{{ avatar_url }}";
                this.family[0].name = this.union.name;
                this.family[0].slogan = this.union.notice;
                this.family[1].slogan = this.slogan_other;
            }
        },
        computed: {
            hasFamily: function () {
                if (this.union !== 0) {
                    return true;
                } else {
                    return false;
                }
            }
        },
        methods: {
            enterUnion: function (index, url) {
                if (this.union && this.union.type == 1) {
                    return;
                }
                location.href = url;
            }
        }
    };
    vm = XVue(opts);
</script>
