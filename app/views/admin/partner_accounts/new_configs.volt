
<form method="POST" class="simple_form new_partner_account ajax_model_form" data-model="partner_account"
      action="/admin/partner_accounts/update_configs" accept-charset="UTF-8" id="new_partner_account" novalidate="novalidate">

    <div class="form-group string optional partner_account_product_channel_id"
         style="padding-left: 2px; padding-right: 2px;float:left;width:50%">
        <label class="string optional control-label" for="partner_account_product_channel_id">产品渠道</label>
        <div>
            <select  text_field="name" value_field="id" name="partner_account[product_channel_id]" id="partner_account_product_channel_id" class="selectpicker select optional form-control" data-live-search="true">
                {{ options(product_channels, '', 'id', 'name') }}
            </select>

        </div>
    </div>
    <div class="form-group string optional partner_account_partner_id"
         style="padding-left: 2px; padding-right: 2px;float:left;width:50%"><label class="string optional control-label" for="partner_account_partner_id">推广渠道</label>
        <div>
            <select  text_field="name" value_field="id" name="partner_account[partner_id]" id="partner_account_partner_id" class="selectpicker select optional form-control" data-live-search="true">
                {{ options(partners, '', 'id', 'name') }}
            </select>
        </div>
    </div>
    <input name="partner_account[id]" value="{{ partner_account.id}}" type="hidden"/>

    <input type="submit" class="btn btn-default " value="保存"/>
    <div class="error_reason" style="color: red;"></div>
</form>


<script type="text/javascript">
    $(function () {
        $('.selectpicker').selectpicker();
    });
</script>