{{ block_begin('head') }}
{{ theme_css('/web/css/main','/web/css/style') }}
{{ theme_js('/web/js/pub_pop') }}
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
            <span class="delete" @click="deleteMusic"></span>
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
                    <td style="width:60px;text-indent: 1em;"><input type="checkbox" id="checkAllChange"></td>
                    {#<td style="width:60px;text-indent: 1em;">#}
                    {#<input type="checkbox" ：checked="fruitIds.length === fruits.length" @click='checkedAll()'></td>#}
                    <td style="width:200px;color: #666666;">歌曲名</td>
                    <td style="width:200px;color: #666666;">演唱者</td>
                    <td style="width:200px;color: #666666;">歌曲</td>
                    <td style="width:120px;color: #666666;">大小</td>
                    <td style="width:180px;color: #666666;">上传时间</td>
                </tr>
                <tr style="height:74px;" v-for="(music,index) in musics" class="music_input_list">
                    <td style="text-indent: 1em;">
                        <input type="checkbox" :true-value="music.id" v-model="delete_list[index]"></td>
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
        <div class="page">
            <a href="#">上一页</a>
            <select>
                <option>1/1页</option>
                <option>1/6页</option>
            </select>
            <a href="#" class="up_next">下一页</a>
        </div>
        <!-- 弹框 开始-->
        <div class="fudong">
            <div class="close_btn close_delete"></div>
            <h3>您确定要删除歌曲吗？</h3>
            <div class="btn_list">
                <span>确定</span>
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
            total_entries: 0,
            musics: [],
            num: [],
            checkedNames: [],
            delete_list: []
        },
        methods: {
            deleteMusic: function () {
                console.log(this.delete_list);
                var data = {delete_list: this.delete_list};
                $.authPost('/web/musics/delete', data, function (resp) {
                    if (resp.error_code != 1) {
                        alert(resp.error_reason);
                    }
                });
            }

        }
    };

    vm = XVue(opts);

    function getList() {
        var data = {page: vm.page, per_page: 4};
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

    $(document).on("click", "#checkAllChange", function () {
        if ($(this).prop("checked")) {
            $("td input[type='checkbox']").prop("checked", true);
        } else {
            $("td input[type='checkbox']").prop("checked", false);
        }
    })

</script>

