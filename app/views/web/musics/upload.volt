{{ block_begin('head') }}
{{ theme_css('/web/css/main','/web/css/style') }}
{{ theme_js('/web/js/xieyi_pop','/js/jquery.form/3.51.0/jquery.form') }}
{{ block_end() }}

<div class="upload_music">
    <div class="music_add" a>
        <form action="/web/musics/upload_music" method="post" enctype="multipart/form-data" class="form" id="upload_music">
            <div class="upload_music_title"><i></i> 歌曲名称 <span>(必填：不超过20个字)</span></div>
            <input type="text" name="name" placeholder="单行输入" required="required">
            <div class="upload_music_title"><i></i> 演唱者 <span>(必填 :不超过20个字 , 该信息不准确可能导致下架)</span></div>
            <input type="text" name="singer_name" placeholder="单行输入" required="required">
            <div class="upload_music_title"><i></i> 音乐文件 <span>(必填 :仅限MP3格式 , 不超过20M)</span></div>
            <div class="select_file">
                <b>选择文件</b>
                <input type="file" name="file" id="file" required="required" accept="audio/mp3"/>
            </div>
            <div class="upload_music_title"><i></i> 音乐类型 <span>(必填 :若为伴奏 , 请选择伴奏)</span></div>
            <select type="text" name="type" id="type">
                {% for key,value in types %}
                    <option value={{ key }}>{{ value }}</option>
                {% endfor %}
            </select>

            <div class="check_xieyi">
                <input type="checkbox" class="checkbox_xy" id="read_agreement" v-model="agreement">
                <label for="read_agreement">我已认真阅读并同意</label>
                <span class="xieyi_pop">《用户上传歌曲伴奏文件协议》</span>
            </div>

            <div class="btn_list music_upload_btn">
                <input type="submit" name="submit" class="close_btn close_right"/>
            </div>
        </form>
    </div>
</div>

<!-- 弹框 开始-->
<div class="fudong">
    <div class="close_btn close_delete"></div>
    <h3>您未同意用户上传歌曲伴奏文件协议，无法上传</h3>
    <div class="btn_list">
        <span class="close_btn close_right">确定</span>
    </div>
</div>
<div class="fudong_bg"></div>


<!-- 弹框结束 -->
<!-- 弹框 开始-->
<div class="xy_fudong xiyi">
    <div class="xieyi_box">
        <div class="close_btn close_delete"></div>
        <h1>用户上传歌曲伴奏协议</h1>
        <h2>用户上传歌曲伴奏及歌词文件不得包含一下违规内容，如违规上传，已经发现将永久删除上传文件，切上传文件的用户将被严惩永久封禁，情节极度恶劣者就追究法律责任。</h2>
        <h4>违规内容：</h4>
        <p>1.反对宪法所确定的基本原则；</p>
        <p>2.危害国家安全，泄露国家秘密，颠覆国家政权，破坏国家统一的；</p>
        <p>3.损害国家荣誉和利益的；</p>
        <p>4.煽动民族仇恨、民族歧视、破坏民族团结的；</p>
        <p>5.破坏国家宗教政策，宣扬邪教和封建迷信的；</p>
        <p>6.散布谣言，扰乱社会秩序，破坏社会稳定的；</p>
        <p>7.散布淫秽、色情、赌博、暴力、凶杀、恐怖或者教唆犯罪的；</p>
        <p>8.侮辱或者诽谤他人，侵害他人合法权利的；</p>
        <p>9.煽动非法集会、结社、游行、示威、聚众扰乱社会秩序的；</p>
        <p>10.以非法民间组织名义活动的；</p>
        <p>11.含有虚假、有害、胁迫、侵害他人隐私、骚扰、侵害、中伤、粗俗、猥亵、或其它道德上令人反感的内容；</p>
        <p>12.含有中国法律、法规、规章、条例以及任何具有法律效力之规范所限制或禁止的其他内容的。</p>
        <div class="btn_list">
            <span class="close_btn close_right">确定</span>
        </div>
    </div>
</div>
<div class="xy_fudong_bg"></div>

<script>
    var opts = {
        data: {
            agreement: true,
            upload_status: false
        },
        methods: {
            aaaa: function () {

            }
        }
    };

    vm = XVue(opts);

    $(function () {

        function colse_fd() {
            $(".fudong").hide();
            $(".fudong_bg").hide();
        };

        function open_fd() {
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
        }

        var doc_height = $(document).height();
        var w_height = $(window).height();
        var w_width = $(window).width();

        $(".fudong").hide();
        $(".fudong_bg").hide();

//        $(".form").submit(function () {
//            if (!vm.agreement) {
//                open_fd();
//                return false;
//            }
//        });

        $(document).on('submit', '#upload_music', function (event) {
            event.preventDefault();
            var self = $(this);
            var url = self.attr("action");

            if (!vm.agreement) {
                open_fd();
                return false;
            }

            self.ajaxSubmit({
                error: function (xhr, status, error) {
                    alert('服务器错误 ' + error);
                },

                success: function (resp, status, xhr) {
                    if (resp.error_url) {
                        location.href = resp.error_url;
                        return;
                    }
                    console.log(resp);
                    alert(resp.error_reason);
                }
            });

            return false;

        });



        $(".close_btn").click(function () {
            colse_fd();
        });

    });

</script>