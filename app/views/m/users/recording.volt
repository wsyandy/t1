{{ block_begin('head') }}
{{ theme_css('/m/css/voice_main.css') }}
{{ theme_js('/js/vue.min.js') }}
{{ block_end() }}
{#<div id="app">#}
<div id="app" class="recording">
    <p class="recording_title">请录制一段不少于5秒的音频</p>
    <div class="recording_title_hint">
        <span class="icon"></span>
        <span>内容可以参考以下文案</span>
        <span class="icon"></span>
    </div>
    <div class="recording_copywriting">
        <h4>一个有趣的文案建议</h4>
        <p>${read_text}</p>
    </div>
    <div class="recording_progress_box">
        <span :style="{width: recordingLength+'%' }" class="recording_progress_bar"></span>
        <span :style="{left: recordingLength+'%' }" class="recording_progress_point">${Math.ceil(recordingLength/10)}s</span>
    </div>
    <div @touchstart="onRecordingStart" @touchend="onRecordingEnd" class="recording_but">
        <span>长按</span>
        <span>录制</span>
    </div>
    <p class="recording_but_hint">完成录制可松开按钮～</p>
    <div v-if="isToast" class="recording_tosat">
        <span class="recording_tosat_icon"></span>
        <span>正在录音</span>
    </div>
    <div v-if="isTosatText" class="toast_text_box">
        <span class="toast_text">录制时间太短，请重新录制</span>
    </div>
    <div v-if="isTosatText" class="recording_analysis">
        <div class="recording_analysis_box">
            <h5>温馨小提示</h5>
            <p>正在分析中，请稍等几秒哟～</p>
            <div class="recording_analysis_box_but">
                <span>疯狂分析中…</span>
            </div>
        </div>
    </div>
</div>
{#</div>#}
<script>
    var opts = {
        data: {
            isToast: false,
            isTosatText: false,
            isAnalysis: false,
            recordingLength:1,
            code:"{{ code }}",
            sid:"{{ sid }}",
            read_text:"{{ read_text }}",
            sex:"{{ sex }}",
            nickname:"{{ nickname }}"
        },

        methods: {
            // 按下
            onRecordingStart(){
                if(this.recordingLength>=100) return false;
                this.times = setInterval(()=>{
                    this.recordingLength++;
                this.isToast = true;
                if(this.recordingLength>=100){
                    clearInterval(this.times);
                    this.onAnalysis();
                }
            },100);
            },
            // 抬起
            onRecordingEnd:function(){
                clearInterval(this.times);
                if(this.recordingLength<40){
                    // 录音时间太短重置
                    this.showToast();
                    this.recordingLength = 1;
                    this.isToast = false;
                    return false;
                }
                this.onAnalysis();
            },
            // 进入分析
            onAnalysis:function(){
                this.isAnalysis = true;
                this.isToast = false;
                setTimeout(function () {
                    var url = '/m/users/voice_identify';
                    vm.redirectAction(url + '?sid=' + vm.sid + '&code=' + vm.code + '&sex=' + vm.sex+'&nickname='+vm.nickname);
                },2000)


            },
            // 文字提示
            showToast:function(){
                var self = this;
                if(this.isTosatText){
                    return false;
                }else{
                    this.isTosatText=true;
                    setTimeout(function(){
                        self.isTosatText=false;
                    },1000);
                }
            }
        }
    };
    vm = XVue(opts);
    $(function () {
        alert(111);
    });
</script>