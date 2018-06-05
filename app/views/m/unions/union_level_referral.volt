{{ block_begin('head') }}
{{ theme_css('/m/css/union_level_detail.css') }}
{{ theme_js('/m/js/resize.js') }}
{{ block_end() }}
<div class="gonglue_box" id="app">
    <h3>一.家族等级如何划分？</h3>
    <p>家族等级共有6个等级，从1星级开始，到6星级结束</p>
    <h3>二.家族等级晋级如何进行考核</h3>
    <p>1.每月进行一次考核</p>
    <p>2.考核规则：</p>
    <div class="text_box">
        <span>家族升星级</span>
        <b>根据家族目前总积分来晋升，家族当月积分必须达到当前星级保级积分标准。</b>
    </div>
    <div class="text_box">
        <span>家族降星级</span>
        <b>当月家族积分未达到保级积分标准时，下个月家族星级降一个星级。</b>
    </div>
    <div class="dengji_list">
        <table class="table">
            <tr class="week_tr_title">
                <td>家庭等级</td>
                <td>晋级积分（分）</td>
                <td>保级积分（分）</td>
            </tr>
            <tr>
                <td>
                    <img src="/m/images/dengji_six.png" alt="">
                    <span>六星级</span>
                </td>
                <td>10000以上</td>
                <td>2000</td>
            </tr>

            <tr>
                <td>
                    <img src="/m/images/dengji_five.png" alt="">
                    <span>五星级</span>
                </td>
                <td>5001-10000</td>
                <td>1500</td>
            </tr>

            <tr>
                <td>
                    <img src="/m/images/dengji_four.png" alt="">
                    <span>四星级</span>
                </td>
                <td>2001-5000</td>
                <td>1000</td>
            </tr>
            <tr>
                <td>
                    <img src="/m/images/dengji_three.png" alt="">
                    <span>三星级</span>
                </td>
                <td>1001-2001</td>
                <td>500</td>
            </tr>
            <tr>
                <td>
                    <img src="/m/images/dengji_two.png" alt="">
                    <span>二星级</span>
                </td>
                <td>501-1000</td>
                <td>300</td>
            </tr>
            <tr>
                <td>
                    <img src="/m/images/dengji_one.png" alt="">
                    <span>一星级</span>
                </td>
                <td>200-500</td>
                <td>150</td>
            </tr>
        </table>
    </div>
    <h3>三.家族积分计算</h3>
    <h4>家族积分核算公式：</h4>
    <p style="color: #333333;padding-bottom:0.4rem;">家族积分=考核厅流水积分+考核厅人气积分+奖励积分－惩罚扣分</p>
    <div class="text_box text_bottom">
        <span>ps</span>
        <b>1. 考核厅流水：通过考核之后的厅，流水每达到一万记1分。</b>
        <b>2. 考核厅人气：考核厅每天开播时长大于2小时记1分，每天最多记1分。</b>
        <b>3. 奖励积分：家族成员积极参与官方活动奖励积分，根据参与情况会有1-5分的奖励。</b>
        <b>4. 惩罚扣分：视情节严重情况扣除1-10分（头像暴露，房名违规，房间内出现色情暴力，等违规行为）</b>
    </div>
</div>
