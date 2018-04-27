<form method="POST" class="simple_form new_partner_data ajax_model_form" data-model="partner_data"
      action="/admin/partner_datas/update/{{ partner_data.id }}" accept-charset="UTF-8" id="new_partner_data" novalidate="novalidate">
    <div class="form-group string optional partner_data_partner_id"
         style="padding-left: 2px; padding-right: 2px;float:left;width:50%"><label class="string optional control-label" for="partner_data_partner_id">推广渠道</label>
        <div>
            <select  text_field="name" value_field="id" name="partner_data[partner_id]" id="partner_data_partner_id" class="selectpicker select optional form-control" data-live-search="true">
                {{ options(partners, partner_data.partner_id, 'id', 'name') }}
            </select>
        </div>
    </div>
    <div class="form-group string optional partner_data_product_channel_id"
         style="padding-left: 2px; padding-right: 2px;float:left;width:50%">
        <label class="string optional control-label" for="partner_data_product_channel_id">产品渠道</label>
        <div>
            <select text_field="name" value_field="id" name="partner_data[product_channel_id]" id="partner_data_product_channel_id" class="selectpicker select optional form-control" data-live-search="true">
                {{ options(product_channels, partner_data.product_channel_id, 'id', 'name') }}
            </select>
        </div>
    </div>
    <div class="form-group string optional partner_data_activated_num"
         style="padding-left: 2px; padding-right: 2px;float:left;width: 100%"><label class="string optional control-label" for="partner_data_activated_num">激活人数</label>
        <div><input type="text" class="  input optional form-control" id="partner_data_activated_num"
                    name="partner_data[activated_num]" value="">
        </div>
    </div>
    <div class="form-group string optional partner_data_start_at"
         style="padding-left: 2px; padding-right: 2px;float:left;width:100%"><label
                class="string optional control-label" for="partner_data_start_at">时间</label>
        <div><input type="text" class=" form_datetime input optional form-control" id="partner_data_start_at"
                    name="partner_data[start_at]" value="{{ partner_data.stat_at_text }}">
        </div>
    </div>
    <input type="submit" class="btn btn-default " value="保存"/>
</form>


<script type="text/javascript">
    $(".form_datetime").datetimepicker({
        language: "zh-CN",
        format: 'yyyy-mm-dd',
        autoclose: 1,
        todayBtn: 1,
        todayHighlight: 1,
        startView: 2,
        minView: 2
    });
</script>


<script type="text/javascript">
    $(function () {
        $('.selectpicker').selectpicker();
    });
</script>