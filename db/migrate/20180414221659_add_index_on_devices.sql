CREATE index product_channel_id_on_devices on devices(product_channel_id);
CREATE index platform_on_devices on devices(platform);
CREATE unique index sid_on_devices on devices(sid);
CREATE index imei_on_devices on devices(imei);
CREATE index partner_id_on_devices on devices(partner_id);
CREATE index device_no_on_devices on devices(device_no);
CREATE index idfa_on_devices on devices(idfa);
CREATE index user_id_on_devices on devices(user_id);
CREATE index geo_province_id_on_devices on devices(geo_province_id);
CREATE index geo_city_id_on_devices on devices(geo_city_id);
CREATE index province_id_on_devices on devices(province_id);
CREATE index city_id_on_devices on devices(city_id);
CREATE index country_id_on_devices on devices(country_id);