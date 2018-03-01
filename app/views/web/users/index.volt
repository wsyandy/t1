{{ block_begin('head') }}
{{ theme_css('/web/css/main','/web/css/style') }}
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
                    <td style="width:60px;text-indent: 1em;"><input type="checkbox" id="checkAllChange"
                                                                    @click="selectAll"></td>
                    <td style="width:200px;color: #666666;">歌曲名</td>
                    <td style="width:200px;color: #666666;">演唱者</td>
                    <td style="width:200px;color: #666666;">歌曲</td>
                    <td style="width:120px;color: #666666;">大小</td>
                    <td style="width:180px;color: #666666;">上传时间</td>
                </tr>
                <tr style="height:74px;" v-for="(music,index) in musics" class="music_input_list">
                    <td style="text-indent: 1em;">
                        <input type="checkbox" :true-value="music.id" v-model="selected_list[index]"></td>
                    <td>${music.name}</td>
                    <td>${music.singer_name}</td>
                    <td>
                        <audio :src="music.file_url" controls="controls" style="width: 160px;">
                            您的浏览器不支持 audio 标签。
                        </audio>
                    </td>
                    <td>${music.file_size}</td>
                    <td>${music.date}</td>
                </tr>
            </table>
        </div>

        <div class="page" v-show="show">
            <div class="pagelist">
                <span class="jump" :class="{disabled:pstart}" @click="jumpPage(--page)">上一页</span>
                <select v-model="selected" @click="jumpPage(selected)">
                    <option v-for="num in indexs" v-bind:value="num">${ num }/${total_page}页</option>
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
    var opts = {
        data: {
            show_music: false,
            page: 1,
            total_page: 1,
            change_page: '',
            total_entries: 0,
            musics: [],
            num: [],
            selected_list: [],
            selected: 1
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
                var data = {delete_list: this.selected_list};
                $.authPost('/web/musics/delete', data, function (resp) {
                    if (resp.error_code != 0) {
                        alert(resp.error_reason);
                    } else {
                        location.reload();
                    }
                });
            },
            selectAll: function (event) {
                console.log(event.currentTarget);
                if (!event.currentTarget.checked) {
                    this.selected_list = [];
                } else { //实现全选
                    this.selected_list = [];
                    this.musics.forEach(function (music) {
                        this.selected_list.push(music.id);
                    }, this);
                }

                console.log(this.selected_list);
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
                    getList();
                }
                vm.change_page = '';
            }
            /*分页器 end*/
        }
    };

    vm = XVue(opts);

    function getList() {
        var data = {page: vm.page, per_page: 10};
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
        getList();
    })

    $(function () {

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
            if (vm.selected_list.length == 0) {
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

