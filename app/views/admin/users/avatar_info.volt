{{ css('list') }}

<ol class="breadcrumb">
    {% if isAllowed('albums','update') %}
        <li><a href="#" class="batch_select" data-target="batch_form" data-select_option="all">全选</a></li>
        <li><a href="#" class="batch_select" data-target="batch_form" data-select_option="reverse">反选</a>
        </li>
        {% if 1 == auth_status and user_id == 1 %}
            <li><a href="#" class="selected_action" data-target="auth_type" data-formid="batch_form"
                   data-action="1">选中为男</a></li>
            <li><a href="#" class="selected_action" data-target="auth_type" data-formid="batch_form"
                   data-action="2">选中为女</a></li>
            <li><a href="#" class="selected_action" data-target="auth_type" data-formid="batch_form"
                   data-action="3">选中为通用</a></li>
        {% else %}
            <li><a href="#" class="selected_action" data-target="auth_status" data-formid="batch_form"
                   data-action="1">选中通过</a></li>
            <li><a href="#" class="selected_action" data-target="auth_status" data-formid="batch_form"
                   data-action="2">选中不通过</a></li>
        {% endif %}
    {% endif %}
</ol>


<div>一共{{ albums.total_entries }}个</div>

{{ form('/admin/albums/batch_update', 'method':'post','class':'form-inline','id':'batch_form','accept-charset':'UTF-8') }}
<div class="row">
    <input name="auth_status" id="auth_status" type="hidden" value="">
    <input name="auth_type" id="auth_type" type="hidden" value="">
    <dl class="thumb_list">
        {% for album in albums %}
            <dd class=" unit object_unit" style="height: 180px; width: 130px;">
                <label for="user_{{ album.id }}">
                    {#<a href="/admin/users/show/{{ album.user_id }}">#}
                    <img alt="Small lmoubofcto" height="150" id="album_{{ album.id }}"
                         src="{{ album.image_small_url }}"
                         width="120"/>
                    {#</a>#}
                </label>
                <p>
                    <input id="user_{{ album.id }}" name="ids[]" type="checkbox" value="{{ album.id }}"
                           autocomplete="off">
                    {% if isAllowed('albums','update') %}
                        {% if 1 == auth_status and user_id == 1 %}
                            <a href="/admin/albums/update/{{ album.id }}?auth_type=1" class='album_once_click'>男</a>
                            <a href="/admin/albums/update/{{ album.id }}?auth_type=2" class='album_once_click'>女</a>
                            <a href="/admin/albums/update/{{ album.id }}?auth_type=3" class='album_once_click'>通用</a>
                        {% else %}
                            <a href="/admin/albums/update/{{ album.id }}?auth_status=1"
                               class='album_once_click'>过</a>
                            <a href="/admin/albums/update/{{ album.id }}?auth_status=2" class='album_once_click'>不过</a>
                        {% endif %}
                    {% endif %}
                </p>
            </dd>
        {% endfor %}
    </dl>
</div>
{{ end_form() }}
{{ pagination(albums) }}

<script>
    $(function () {
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
        $(".album_once_click").click(function (event) {
            event.stopPropagation();
            event.preventDefault();
            var self = $(this);
            var url = self.attr("href");
            $.get(url, function (resp) {
                self.parents(".unit,.object_unit").remove();
            });
            return false;
        });
    })
</script>



