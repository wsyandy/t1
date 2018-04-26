{{ block_begin('head') }}
{{ theme_css('/m/css/union_main','/m/css/union_add') }}
{{ theme_js('/js/jquery.form/3.51.0/jquery.form') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak>
    <form action="/m/unions/create?sid={{ sid }}&code={{ code }}" method="post" enctype="multipart/form-data"
          class="form" id="create_union">
        <div class="family-logo">
            <img src="" class="ico-img-update" id="img_preview" :src="img_update">
            <span>${ isEdit?'点击更换':'点击添加' }</span>
            <input class="img_update" type="file" id="avatar_file" required="required"
                   name="avatar_file" accept="image/*" capture="camera">
        </div>

        <div class="family-edit">
            <ul>
                <li>
                    <span>家族名称 </span>
                    <input class="input_text" maxlength="10" type="text" placeholder="最多输入10个字"
                           name="name" id="name">
                </li>
                <li>
                    <span>家族公告 </span>
                    <div class="textarea_text">
                        <textarea name="notice" maxlength="50" placeholder="最多输50个字"
                                  onpropertychange="this.style.height=this.scrollHeight + 'px'"
                                  oninput="this.style.height=this.scrollHeight + 'px'" id="notice"></textarea>
                    </div>
                </li>

                <li class="select">
                    <span>家族设置 </span>
                    <div class="select_area" @click="setSelect">
                        <span>${options[selected].text}</span>
                        <input type="hidden" v-model="selected" name="need_apply">
                    </div>
                </li>

            </ul>
            <div class="agree_div">
                <img class="agree_img" :src="set_select" @click="agreeSelect"/>
                <div class="agree_text">
                    <span class="agree_txt">阅读并同意</span>
                    <span class="agree_txt" @click="agreement">《家族使用协议》</span></div>
            </div>

            {#<div class="family-btn" :style="{backgroundColor: hasAgree?'#FDC8DA':'#F45189'}" >#}
            {#<input class="close_submit" type="submit" name="submit" value="申请创建（100钻石）"#}
            {#:style="{backgroundColor: hasAgree?'#FDC8DA':'#F45189'}">#}
            {#</div>#}
            <div class="family-btn" :style="{backgroundColor: hasAgree?'#FDC8DA':'#F45189'}">
                <input class="close_submit" type="submit" name="submit" value="">

                <span>申请创建（10000钻石）</span>
            </div>

        </div>
    </form>

    <div class="popup_cover" v-if="isPop">
        <div class="popup_box">
            <img class="ico-warn" src="/m/images/ico-warn.png" alt="">
            <div class="popup_text" id="popup_text">
                创建家族需要支付10000钻石，您的钻石数量不足，请先充值
            </div>
            <div class="popup_btn">
                <a class="btn_cancel" href="#" @click.stop="establishFamily(0)">取消</a>
                <a class="btn_recharge" href="#" @click.stop="establishFamily(1)">前往充值</a>
            </div>
        </div>
    </div>

    <div :class="[isSet ? '' : 'fixed', 'popup_cover']">
        <div :class="[isSet ? '' : 'fixed', 'pop_bottom']">
            <ul>
                <li v-for="(option, index) in options" @click="setSelected(index)"> ${ option.text }</li>
            </ul>
            <div class="close_btn" @click="cancelSelect">取消</div>
        </div>
    </div>

</div>
<script>
    //解决上传图片时capture="camera"在安卓与IOS的兼容性问题（在IOS只能拍照，不能选相册）
    var ua = navigator.userAgent.toLowerCase();//获取浏览器的userAgent,并转化为小写——注：userAgent是用户可以修改的
    var isIos = (ua.indexOf('iphone') != -1) || (ua.indexOf('ipad') != -1);//判断是否是苹果手机，是则是true

    var opts = {
        data: {
            isEdit: false,
            isPop: false,
            isSet: false,
            options: [
                {text: '任何人可加入', value: 0},
                {text: '申请才可加入', value: 1}
            ],
            selected: 1,
            img_update: '/m/images/ico-img-update.png',
            set_select: '/m/images/ico-select.png',
            hasAgree: true,
            sid: '{{ sid }}',
            code: '{{ code }}',
            is_development: '{{ isDevelopmentEnv() }}'
        },
        created: function () {
        },
        methods: {
            agreeSelect: function () {
                if (this.hasAgree) {
                    this.hasAgree = false;
                    this.set_select = '/m/images/ico-selected.png';
                } else {
                    this.hasAgree = true;
                    this.set_select = '/m/images/ico-select.png';
                }
            },


            establishFamily: function (index) {
                this.isPop = false;

                if (isIos && !vm.is_development) {
                    alert("请到我的账户充值");
                    return;
                }

                if (index == 1) {
                    var url = "/m/products&sid=" + vm.sid + "&code=" + vm.code;
                    location.href = url;
                    return false;
                }
            },
            setSelect: function () {
                this.isSet = true
            },
            cancelSelect: function () {
                this.isSet = false
            },
            setSelected: function (index) {
                console.log(index);
                this.selected = this.options[index].value;
                this.isSet = false
            },
            agreement: function () {
                var url = "/m/unions/agreement&sid=" + vm.sid + "&code=" + vm.code;
                location.href = url;
            }
        }
    };

    vm = XVue(opts);

    var can_create = true;
    $(document).on('submit', '#create_union', function (event) {
        event.preventDefault();
        if (can_create == false) {
            return false;
        }

        can_create = false;

        var self = $(this);

        var fileSize = $('#avatar_file')[0].size;
        console.log(fileSize);
        if (!fileSize) {
            alert("请选择图片");
            can_create = true;
            return false;
        }

        var name_length = $("#name").val().length;

        if (name_length == 0) {
            alert("家族名称不能为空");
            can_create = true;
            return false;
        }

        if (vm.hasAgree) {
            can_create = true;
            return false;
        }

        self.ajaxSubmit({
            error: function (xhr, status, error) {
                alert('服务器错误 ' + error);
                can_create = true;
            },

            success: function (resp, status, xhr) {
                can_create = true;

                if (resp.error_code == -400) {
                    vm.isPop = true;
                    return false;
                } else if (resp.error_code != 0) {
                    alert(resp.error_reason);
                    return false;
                } else {
                    window.history.back(-1);
                }

            }
        });

        return false;
    });

    $(function () {
        if (isIos) {
            $("input:file").removeAttr("capture");
        }

        /*上传单张图片 start*/
        $('#avatar_file').change(function () {
            //检验是否为图像文件
            if (/image\/\w+/.test($(this)[0].files[0].type)) {
                if ($(this)[0].files && $(this)[0].files[0]) {
                    var reader = new FileReader();
                    //将文件以Data URL形式读入页面
                    reader.readAsDataURL($(this)[0].files[0]);
                    reader.onload = function (e) {
                        //载入文件
                        $("#img_preview").attr("src", e.target.result);

                    }
                }
            } else {
                alert("这里需选择图片！");
                return false;
            }
        });
        /*上传单张图片 end*/
    });

</script>


