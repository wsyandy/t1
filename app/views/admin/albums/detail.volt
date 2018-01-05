{{ css('list') }}
<div class="row">
    <div class="col-md-12">
        <a href="#" class="batch_select btn btn-sm" data-target="batch_form" data-select_option="all">全选</a>
        <a href="#" class="batch_select btn btn-sm" data-target="batch_form" data-select_option="reverse">反选</a>
        <a href="#" class="selected_action btn btn-sm" data-target="image_auth" data-formid="batch_form"
           data-action="1">选中通过</a>
        <a href="#" class="selected_action btn btn-sm" data-target="image_auth" data-formid="batch_form"
           data-action="2">选中不通过</a>
        <a href="/admin/albums/yellow" class="btn btn-sm">黄图列表</a>
    </div>
</div>

<div>一共{{ albums.total_entries }}个</div>

{{ form('/admin/albums/batch', 'method':'post','class':'form-inline','id':'batch_form','accept-charset':'UTF-8') }}
<div class="row">
    <input name="image_auth" id="image_auth" type="hidden" value="">
    <dl class="thumb_list">
        {% for album in albums %}
            <dd class=" unit object_unit" style="height: 180px; width: 130px;">
                <label for="user_{{ album.id }}">
                    <a href="/admin/users/show/{{ album.user_id }}">
                        <img alt="Small lmoubofcto" height="150" id="album_{{ album.id }}" src="{{ album.small_url }}"
                             width="120"/>
                    </a>
                </label>
                <p>
                    <input id="user_{{ album.id }}" name="ids[]" type="checkbox" value="{{ album.id }}" autocomplete="off">
                    <a href="/admin/albums/update/{{ album.id }}?image_auth=1" class='album_once_click'>过</a>
                    <a href="/admin/albums/update/{{ album.id }}?image_auth=2" class='album_once_click'>不过</a>
                </p>
            </dd>
        {% endfor %}
    </dl>
</div>
{{end_form()}}
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
        $(".batch_pass").click(function (event) {
            event.preventDefault();
            event.stopPropagation();
            var ids = "";
            var target = $(this).data("target");
            $("#" + target + " input:checkbox").each(function () {
                ids = ids + "," + $(this).val();
            });
            var token = $("meta[name='csrf-token']").attr("content");
            var form = $("#" + target).parent("form");
            console.log(form);
            var action = $(this).data("action");

            /*$(".object_unit").remove();*/
            var c = $(this);
            $(this).attr({"disabled": "disabled"});
            $.ajax({
                url: action,
                type: 'post',
                data: {'ids': ids, 'authenticity_token': token},
                success: function (data) {
                    $(".object_unit").remove();
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



