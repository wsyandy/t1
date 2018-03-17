{{ block_begin('head') }}

{{ weixin_css('service') }}

{{ block_end() }}

<div class="service">
    <h3>客服中心：</h3>
    <p>  尊敬的用户，您有任何问题，可以致电客服中心
        我们将竭诚为您服务:</p>
    <h2>全国统一客服电话:<a href="tel:{{ product_channel.service_phone }}">{{ product_channel.service_phone }}</a></h2>
    <h2>工作时间：8:00-21:00 (周一至周日)</h2>
</div>