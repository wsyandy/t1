{{ block_begin('head') }}
{{ theme_css('/web/css/style','/web/css/jquery.searchableSelect') }}
{{ theme_js('/web/js/xieyi_pop','/js/jquery.form/3.51.0/jquery.form','/web/js/jquery.searchableSelect') }}
{{ block_end() }}

<div class="upload_music">
    <a href="/web/users/index" class="music_list_head">
        <img src="/web/images/fanhui.png">
        <span>返回歌曲列表</span>
    </a>
    <div class="music_add">
        <form action="/web/musics/upload_music" method="post" enctype="multipart/form-data" class="form"
              id="upload_music">
            <div class="upload_music_title"><i></i> 歌曲名称 <span>(必填：不超过10个字)</span></div>
            <input type="text" name="name" placeholder="单行输入" required="required" id="name">
            <div class="upload_music_title"><i></i> 演唱者 <span>(必填 :不超过20个字 , 该信息不准确可能导致下架)</span></div>
            <input type="text" name="singer_name" placeholder="单行输入" required="required" id="singer_name">
            <div class="upload_music_title"><i></i> 音乐文件 <span>(必填 :仅限MP3格式 , 不超过20M)</span></div>
            <div class="select_file_box">
                <div class="select_file">
                    <b>选择文件</b>
                    <input type="file" name="music[file]" id="file" required="required" accept="audio/mp3"/>
                </div>
                <div class="file_name" id="file_name">
                </div>

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
                <input type="submit" name="submit" class="close_btn close_right" value="确认上传">
                <img src="/web/images/jindutiao.gif" id="jindutiao">
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
        methods: {}
    };

    vm = XVue(opts);

    var browserCfg = {};

    function Version() {
        var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
        //判断是否IE<11浏览器
        if (userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1) {
            browserCfg.ie = true;
        } else {
            browserCfg.other = true;
        }
    }

    $(function () {
        $("#jindutiao").hide();

        Version();

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

        var can_upload = true;

        $(document).on('submit', '#upload_music', function (event) {
            event.preventDefault();
            if (can_upload == false) {
                return false;
            }

            can_upload = false;

            var self = $(this);
            var url = self.attr("action");

            if (!vm.agreement) {
                can_upload = true;
                open_fd();
                return false;
            }

            if (browserCfg.other) {
                var fileSize = $('#file')[0].files[0].size;
                console.log(fileSize);
                if (fileSize > 20 * 1024 * 1024) {
                    alert("歌曲不能大于20M");
                    can_upload = true;
                    return false;
                }
            }

            $("#jindutiao").show();

            self.ajaxSubmit({
                error: function (xhr, status, error) {
                    alert('服务器错误 ' + error);
                    $("#jindutiao").hide();
                },

                success: function (resp, status, xhr) {
                    can_upload = true;
                    $("#jindutiao").hide();
                    if (resp.error_url) {
                        location.href = resp.error_url;
                        return;
                    }
                    alert(resp.error_reason);
                }
            });

            return false;

        });


        $(".close_btn").click(function () {
            colse_fd();
        });

        $('select').searchableSelect();

        $('#file').change(function () {
            if ($(this)[0].files && $(this)[0].files[0]) {
                $("#file_name").html($(this)[0].files[0].name);
            }
        });
    });

</script>