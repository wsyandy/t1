{{ block_begin('head') }}
{{ theme_css('/web/css/style','/web/css/audio_player','/web/css/iconfont') }}
{{ theme_js('/web/js/vue-resource.min') }}
{{ block_end() }}

<div class="person">
    <div class="person_left">
        <div class="person_pic">
            <img src={{ user.avatar_url }}>
        </div>
        <div class="name">
            <h3>{{ user.nickname }}</h3>
            <p>Hi~ID:{{ user.id }}</p>
        </div>
    </div>
    <div class="person_right">已上传：${total_entries}首</div>
</div>
<div class="music_box">
    <div class="music_top">
        <h3>歌曲列表：</h3>
        <div class="top_right">
            <a href="/web/musics/upload">上传音乐</a>
            <span class="delete"></span>
        </div>
    </div>

    <div class="music_none" v-show="!musics.length">
        <img src="/web/images/music_none.png">
        <p>这里空空如也！快点去上传些音乐吧~</p>
    </div>

    <div v-show="musics.length">
        <div class="music_list">
            <table>
                <tr style="height:40px;">
                    <td style="width:60px;text-indent: 1em;">
                        <input type="checkbox" :checked="checked_list.length==musics.length" @click="selectAll">
                    </td>
                    <td style="width:200px;color: #666666;">歌曲名</td>
                    <td style="width:200px;color: #666666;">演唱者</td>
                    <td style="width:200px;color: #666666;">歌曲</td>
                    <td style="width:120px;color: #666666;">大小</td>
                    <td style="width:180px;color: #666666;">上传时间</td>
                </tr>
                <tr style="height:74px;" class="audio_box" v-for="(item,index) in musics">
                    <td style="text-indent: 1em;">
                        <input type="checkbox" :value="item.id" v-model="checked_list"></td>
                    <td>${item.name}</td>
                    <td>${item.singer_name}</td>
                    <td>
                        <div class="audio_box" >
                            {#<input type="checkbox" class="check_box" :id="'id'+item.id" :value="item.id" v-model="checked_list">#}
                            {#<label :for="'id'+item.id"></label>#}
                            <!--播放/暂停按钮-->
                            <div :class="['iconfont',{ 'btn_play': item.isPlay }  ,{'btn_pause': !item.isPlay }]"
                                 @click="audioPlay($event,index)"></div>
                            <!--模拟音频进度条-->
                            <div class="music-nav">
                                <!--进度条-->
                                <div class="audio_progress">
                                    <span class="audio_line"></span>
                                    <span class="audio_blue" :style="{width: item.leftDot+item.wDot + 'px'}"></span>
                                    <span class="audio_dot" :style="{left: item.leftDot+ 'px'}"></span>
                                    <input type="range" min="0" :max="Math.round(item.duration)" step="0.4" class="scale"
                                           @change="scaleChange($event,index)">
                                </div>
                                <!--HTML5音频标签 不设置控制属性使其不显示-->
                                <audio class="music" :src="item.file_url">
                                    Your browser does not support HTML5 audio.
                                </audio>
                            </div>
                            <!--当前时间-->
                            <div class="time time_cur">${ toTwo(item.currentTime) }</div>
                            <div class="time_line">/</div>
                            <!--歌曲时长-->
                            <div class="time time_long">${ toTwo(item.duration) }</div>
                        </div>
                    </td>
                    <td>${item.file_size}</td>
                    <td>${item.date}</td>
                </tr>

            </table>
       <div/>

        <div class="page" v-show="show">
            <div class="pagelist">
                <span class="jump" :class="{disabled:pstart}" @click="jumpPage(--page)">上一页</span>
                <select v-model="page" @click="jumpPage(page)">
                    <option v-for="num in indexs" v-bind:value="num" v-text='num+"/"+total_page+"页"'></option>
                </select>
                <span :class="{disabled:pend}" class="jump" @click="jumpPage(++page)">下一页</span>
            </div>
        </div>

        <!-- 弹框 开始-->
        <div class="fudong">
            <div class="close_btn close_delete"></div>
            <h3>您确定要删除歌曲吗？</h3>
            <div class="btn_list">
                <span class="close_btn" @click="deleteMusic">确定</span>
                <span class="close_btn close_right right_60">取消</span>
            </div>
        </div>
        <div class="fudong_bg"></div>
    </div>

</div>

<!-- 弹框结束 -->

<script>
    var playtimer;

    var opts = {
        data: {
            show: false,
            page: 1,
            total_page: 1,
            change_page: '',
            total_entries: 0,
            musics: [],
            checked_list: [],
            playtimer: null
        },
        created: function () {
            this.getMusic();
        },
        computed: {
            /*分页器 start*/
            show: function () {
                return this.total_page && this.total_page !== 1
            },
            pstart: function () {
                return this.page === 1;
            },
            pend: function () {
                return this.page === this.total_page;
            },
            indexs: function () {
                ar = [];
                var page = 1;
                var total_page = this.total_page;
                while (total_page >= 1) {
                    ar.push(page);
                    page++;
                    total_page--;
                }
                return ar;
            }
            /*分页器 end*/
        },
        methods: {
            deleteMusic: function () {
                var data = {delete_list: this.checked_list};
                $.authPost('/web/musics/delete', data, function (resp) {
                    if (resp.error_code != 0) {
                        alert(resp.error_reason);
                    } else {
                        location.reload();
                    }
                });
            },
            selectAll: function () {
                if (this.checked_list.length === this.musics.length) {
                    // 全不选
                    this.checked_list = [];
                } else {
                    this.checked_list = [];
                    var _this = this;
                    // 全选
                    this.musics.forEach(function (item) {
                        _this.checked_list.push(item.id);
                    })
                }
            },
            /*分页器 start*/
            jumpPage: function (id) {
                var int_id = parseInt(id);
                if (!isNaN(int_id)) {
                    if (int_id >= this.total_page) {
                        this.page = this.total_page;
                    } else if (int_id <= 1) {
                        this.page = 1;
                    } else {
                        this.page = int_id;
                    }
                    this.getMusic();
                }
                vm.change_page = '';
            },
            /*分页器 end*/
            audioPlay: function (e, index) {
                clearInterval(playtimer);
                var music = document.querySelectorAll(".music");
                var _this = this;
                /*当前播放状态*/
                var curIsPlay = this.musics[index].isPlay;
                this.musics.forEach(function (item, i) {
                    music[i].pause();
                    _this.$set(item, 'isPlay', false);
                });

                // this.$set(this.musics[index],'isPlay', curIsPlay ? false : true );
                // curIsPlay?music[index].pause():music[index].play();
                if (curIsPlay) {
                    this.$set(this.musics[index], 'isPlay', false);
                    music[index].pause()
                } else {
                    this.$set(this.musics[index], 'isPlay', true);
                    music[index].play();
                    var wLine = music[index].parentNode.querySelector('.audio_progress').offsetWidth;
                    var wDot = music[index].parentNode.querySelector('.audio_dot').offsetWidth;

                    var max = Math.round(this.musics[index].duration);
                    playtimer = setInterval(function () {
                        _this.$set(_this.musics[index], 'currentTime', music[index].currentTime);

                        var value = Math.round(_this.musics[index].currentTime);
                        // console.log("歌曲时长：" + max + "~~~~~~~现在的时间：" + value);
                        _this.$set(_this.musics[index], 'leftDot', wLine * value / max);
                        _this.$set(_this.musics[index], 'wDot', wDot);

                        if (value === max) {
                            clearInterval(playtimer);
                            _this.$set(_this.musics[index], 'isPlay', false);
                            _this.$set(_this.musics[index], 'currentTime', 0);
                            _this.$set(_this.musics[index], 'leftDot', 0);
                        }

                    }, 100);

                }

            },
            getMusic: function () {
                var data = {page: this.page, per_page: 10};
                $.authGet('/web/musics/list', data, function (resp) {
                    vm.musics = [];
                    vm.total_page = resp.total_page;
                    vm.total_entries = resp.total_entries;
//                        $.each(resp.musics, function (index, item) {
//                            vm.musics.push(item);
//                        });
                    vm.musics = resp.musics;
                    console.log("111", vm.musics);
                    vm.$nextTick(function () {
//                        var _this = this;
                        var music = document.querySelectorAll(".music");
                        vm.musics.forEach(function (item, i) {
                            // 初始化当前播放时间和时长
                            vm.$set(item, 'currentTime', 0);
                            vm.$set(item, 'duration', 0);
                            // 初始化模拟进度条位置
                            vm.$set(item, 'leftDot', 0);
                            /*设置播放状态isPlay，初始为false*/
                            vm.$set(item, 'isPlay', false);
                            // 获取音频时长设置为 musics自定义属性duration
                            getTime();
                            function getTime() {
                                setTimeout(function () {
                                    if (isNaN(music[i].duration)) {
                                        getTime();
                                    } else {
                                        vm.$set(item, 'duration', music[i].duration);
                                    }
                                }, 100);
                            }
                            console.log(item.id);
                        });
                    });
                });


            },
            scaleChange: function (e, index) {
                var music = document.querySelectorAll(".music");
                if (this.musics[index].isPlay) {
                    music[index].currentTime = e.target.value;
                }
            },
            toTwo: function (num) {  // 转换时间格式
                function changInt(num) {
                    return (num < 10) ? '0' + num : num;
                }

                return changInt(parseInt(num / 60)) + ":" + changInt(Math.floor(num % 60));
            }
        }
    };

    vm = XVue(opts);

    function getList() {
        var data = {page: vm.page, per_page: 1};
        $.authGet('/web/musics/list', data, function (resp) {
            vm.musics = [];
            vm.total_page = resp.total_page;
            vm.total_entries = resp.total_entries;
            $.each(resp.musics, function (index, item) {
                vm.musics.push(item);
            })
        })
    }

    $(function () {
//        getList();

        function colse_fd() {
            $(".fudong").hide();
            $(".fudong_bg").hide();
        };

        $(".fudong").hide();
        $(".fudong_bg").hide();
        var doc_height = $(document).height();
        var w_height = $(window).height();
        var w_width = $(window).width();

        $(".delete").click(function () {
            console.log(vm.checked_list);
            console.log(vm.musics);
            if (vm.checked_list.length == 0) {
                alert("您没有选择文件");
                return
            }

            $(".fudong").show();
            $(".fudong_bg").show();

            $(".fudong_bg").attr("style", "height:" + doc_height + "px");
            var div_width = $(".fudong").width();
            var div_height = $(".fudong").height();

            var div_left = w_width / 2 - div_width / 2 + "px";
            var div_top = w_height / 2 - div_height / 2 + "px";

            $(".fudong").css({
                "left": div_left,
                "top": div_top
            });
        })


        $(".close_btn").click(function () {
            colse_fd();
        });

    });

</script>

