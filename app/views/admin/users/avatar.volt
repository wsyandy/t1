{{ css('list') }}

<ol class="breadcrumb">
    {% if isAllowed('users','auth') %}
        <li><a href="#" class="batch_select" data-target="batch_form" data-select_option="all">全选</a></li>
        <li><a href="#" class="batch_select" data-target="batch_form" data-select_option="reverse">反选</a>
        </li>
        <li><a href="#" class="selected_action" data-target="auth_status" data-formid="batch_form"
               data-action="1">选中通过</a></li>
        <li><a href="#" class="selected_action" data-target="auth_status" data-formid="batch_form"
               data-action="2">选中不通过</a></li>
        <li><a href="/admin/users/avatar?avatar_status=1">已审核列表</a></li>
    {% endif %}
</ol>

<div>一共{{ users.total_entries }}个</div>
{{ form('/admin/users/batch_update_avatar', 'method':'post','class':'form-inline','id':'batch_form','accept-charset':'UTF-8') }}
<div class="row">
    <input name="avatar_status" id="avatar_status" type="hidden" value="">
    <dl class="thumb_list">
        {% for user in users %}
            <dd class="unit object_unit" style="height: 180px; width: 130px;" id="avatar_user_{{ user.id }}">
                <label for="user_{{ user.id }}">
                    <a href="/admin/users/detail/{{ user.id }}">
                        <img alt="Small lmoubofcto" height="150" id="avatar_{{ user.id }}" src="{{ user.avatar_url }}"
                             width="120"/>
                    </a>
                </label>
                <p>
                    <input id="user_{{ user.id }}" name="ids[]" type="checkbox" value="{{ user.id }}"
                           autocomplete="off">
                    {{ user.sex ? '男' : '女' }}({{ user.age }})
                    <a href="/admin/users/auth?id={{ user.id }}&avatar_status=1" class='auth_click'
                       data-user_id="{{ user.id }}">过</a>
                    <a href="/admin/users/auth?id={{ user.id }}&avatar_status=2" class='auth_click'
                       data-user_id="{{ user.id }}">不过</a>
                </p>
            </dd>
        {% endfor %}
    </dl>
</div>
{{ end_form() }}
{{ pagination(users) }}
<script>
    $(function () {
        $('.auth_click').click(function (event) {
            event.preventDefault();
            var self = $(this);
            var url = self.attr("href");
            $.get(url, function (resp) {
                if (resp.redirect_url) {
                    top.window.location.href = resp.redirect_url;
                    return;
                }
                if (resp.error_url) {
                    top.window.location.href = resp.error_url;
                    return;
                }

                var user_id = self.data('user_id');
                $("#avatar_user_" + user_id).remove();
            });
            return false;
        });
        $(".batch_select").click(function (event) {
            event.preventDefault();
            event.stopPropagation();
            var target = $(this).data("target");
            var option = $(this).data("select_option");
            if (option == "all") {
                $("#" + target + " input:checkbox").each(function () {
                    $(this).prop("checked", true);
                });
            }
            if (option == "reverse") {
                $("#" + target + " input:checkbox").each(function () {
                    if ($(this).is(":checked")) {
                        $(this).prop("checked", false);
                    } else {
                        $(this).prop("checked", true);
                    }
                });
            }
        });
        $(".selected_action").click(function (event) {
            event.preventDefault();
            event.stopPropagation();

            var target = $(this).data("target");
            var value = $(this).data("action");
            var form_id = $(this).data("formid");
            $("#" + target).val(value);
            var form = $("#" + form_id);
            var c = $(this);
            $(this).attr({"disabled": "disabled"});
            form.ajaxSubmit({
                success: function () {
                    form.find("input:checked").parents(".object_unit").remove();
                    c.removeAttr("disabled");
                }
            });
        });
    })
</script>



