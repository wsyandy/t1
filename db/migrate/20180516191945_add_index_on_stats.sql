create index stat_at_product_channel_id_on_stats on stats(stat_at,time_type,province_id,platform,version_code,product_channel_id,partner_id,sex);

DROP INDEX product_id_on_stats;