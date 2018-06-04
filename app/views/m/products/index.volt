
{#{{ partial("m/template/" ~ code ~ "_product") }}#}

{% if isDevelopmentEnv() %}
    {{ partial("m/template/" ~ code ~ "_test_product") }}
{% else %}
    {{ partial("m/template/" ~ code ~ "_product") }}
{% endif %}