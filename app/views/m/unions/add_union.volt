{{ block_begin('head') }}
{{ theme_css('/m/css/union_main','/m/css/union_add') }}
{{ theme_js('/js/jquery.form/3.51.0/jquery.form') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak>
    <form action="/m/unions/create?sid={{ sid }}&code={{ code }}" method="post" enctype="multipart/form-data"
          class="form" id="create_union">
        <div class="family-logo">
            <img src="" class="ico-img-update" id="img_preview" :src="isEdit?family_info.ico:img_update">
            <span>${ isEdit?'点击更换':'点击添加' }</span>
            <input class="img_update" type="file" required="required" id="avatar_file"
                   name="avatar_file" accept="image/*" capture="camera">
        </div>

        <div class="family-edit">
            <ul>
                <li>
                    <span>家族名称 </span>
                    <div class="family_name" v-show="isEdit"> ${ family_info.name }</div>
                    <input v-show="!isEdit" class="input_text" maxlength="5" type="text" placeholder="最多输入5个字"
                           name="name">
                </li>
                <li>
                    <span>家族公告 </span>
                    <div class="family_name" v-show="isEdit"> ${ family_info.slogan }</div>
                    <input v-show="!isEdit" class="input_text" maxlength="50" type="text" placeholder="最多输入50个字"
                           name="notice">
                </li>

                <li class="select">
                    <span>家族设置 </span>
                    <div class="select_area">
                        <select v-model="selected">
                            <option v-for="option in options" v-bind:value="option.value">
                                ${ option.text }
                            </option>
                        </select>
                    </div>
                </li>
            </ul>
            <div class="agree_div" @click="agreeSelect">
                <img class="agree_img" :src="set_select"/>
                <div class="agree_text">
                    <span class="agree_txt">阅读并同意</span>
                    <span class="agree_txt">《家族使用协议》</span></div>
            </div>

            <div class="family-btn" :style="{backgroundColor: hasAgree?'#FDC8DA':'#F45189'}">
                <input type="submit" name="submit" value="申请创建（100钻石）"
                       :style="{backgroundColor: hasAgree?'#FDC8DA':'#F45189'}">
                {#<span>${ isEdit?'保存修改':'申请创建（100钻石）' } </span>#}
            </div>

            <div class="popup_cover" v-if="isPop">
                <div class="popup_box">
                    <img class="ico-warn" src="images/ico-warn.png" alt="">
                    <div class="popup_text">
                        创建家族需要支付100钻石，您的钻石数量不足，请先充值
                    </div>
                    <div class="popup_btn">
                        <a class="btn_cancel" href="#" @click="establishFamily(0)">取消</a>
                        <a class="btn_recharge" href="#" @click="establishFamily(1)">前往充值</a>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
<script>
    var opts = {
        data: {
            isEdit: false,
            isPop: false,
            selected: 1,
            options: [
                {text: '任何人可加入', value: 0},
                {text: '申请才可加入', value: 1}
            ],
            family_info: {
                ico: 'images/avatar.jpg',
                name: '二逼青年欢乐多',
                slogan: '花瓣网, 设计师寻找灵感的天堂!图片素材领导者,帮你采集,发现网络上你喜欢的事物.你可以用它收集灵感,保存有用的素材'
            },
            img_update: '/m/images/ico-img-update.png',
            set_select: '/m/images/ico-select.png',
            hasAgree: true,
            agreement: true,
            sid: '{{ sid }}',
            code: '{{ code }}'
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

                if (index == 1) {
                    var url = "/m/products&sid=" + vm.sid + "&code=" + vm.code;
                    location.href = url;
                }
            }
        }
    };

    vm = XVue(opts);

    var can_create = true;
    $(document).on('submit', '#create_union', function (event) {
        event.preventDefault();
        console.log("aaaaa");
        if (can_create == false) {
            return false;
        }

        can_create = false;

        var self = $(this);

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
                } else {
                    alert(resp.error_reason);
                }
            }
        });

        return false;
    });

    $(function () {
        //解决上传图片时capture="camera"在安卓与IOS的兼容性问题（在IOS只能拍照，不能选相册）
        var ua = navigator.userAgent.toLowerCase();//获取浏览器的userAgent,并转化为小写——注：userAgent是用户可以修改的
        var isIos = (ua.indexOf('iphone') != -1) || (ua.indexOf('ipad') != -1);//判断是否是苹果手机，是则是true
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


