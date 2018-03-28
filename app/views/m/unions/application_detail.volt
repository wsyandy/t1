{{ block_begin('head') }}
{{ theme_css('/m/css/union_main','/m/css/application_details') }}
{{ block_end() }}

<div class="application_details_box">
    <div class="applicat_wrap">
        <div class="img">
            <img src="{{ user.avatar_small_url }}">
        </div>
        <h3>{{ user.nickname }}
            {% if user.sex %}
                <span class="men">
                    {% if user.age %}
                        {{ user.age }}
                    {% endif %}
                </span>
            {% else %}
                <span class="women">
                    {% if user.age %}
                        {{ user.age }}
                    {% endif %}
                </span>
            {% endif %}
        </h3>
        <div class="love_wealth">
            <span>魅力值：{{ user.charm_value }}</span>
            <span>财富值：{{ user.wealth_value }}</span>
        </div>
        <h4>申请加入家族</h4>
        <div class="application_btn">
            <span class="refuse">拒绝</span>
            <span class="agree">同意</span>
        </div>
    </div>
</div>


<script>

    var can_click = true;
    $(function () {
        function handelApplication(status, _this) {
            if (can_click == false) {
                return;
            }
            can_click = false;
            var data = {
                sid: "{{ sid }}",
                code: "{{ code }}",
                user_id: {{ user.id }},
                status: status
            };
            $.authPost("/m/unions/handel_application", data, function (resp) {
                can_click = true;
                if (resp.error_code != 0) {
                    alert(resp.error_reason);
                } else {
                    _this.siblings().remove();
                }
            });
        }

        var apply_status = {{ user.apply_status }};
        if (apply_status == -1) {
            $('.refuse').addClass('refuse_selected').text('已拒绝');
            $('.refuse').siblings().remove();
        } else if (apply_status == 1) {
            $('.agree').addClass('refuse_selected').text('已同意');
            $('.agree').siblings().remove();
        }


        $('.refuse').click(function () {
            $(this).addClass('refuse_selected').text('已拒绝');
            handelApplication(-1, $(this));
        });

        $('.agree').click(function () {
            $(this).addClass('refuse_selected').text('已同意');
            handelApplication(1, $(this));
        })
    })

</script>