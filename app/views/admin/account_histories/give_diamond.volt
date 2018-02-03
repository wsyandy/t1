<form action="/admin/account_histories/give_diamond?user_id={{ user_id }}" method="post">
    <div class="form-group">
        <label for="diamond">赠送数量(一次最多赠送100个)</label>
        <input type="text" name="diamond" class="form-control">
    </div>
    <div class="form-group">
        <input type="submit" value="保存" class="btn btn-default">
    </div>
</form>

{% set f = simple_form('/admin/account_histories/give_diamond?user_id='~user_id, ['class': 'ajax_model_form','data-model':'account_history']) %}

{{ f.input('diamond',['label':'赠送数量(一次最多赠送100个)']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('提交') }}

{{ f.end }}