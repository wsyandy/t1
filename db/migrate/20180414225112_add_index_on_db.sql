create index province_id_on_cities on cities(province_id);
create index clazz_on_sms_channels on sms_channels(clazz);

CREATE  index hi_coin_history_id_on_account_histories on account_histories(hi_coin_history_id);
CREATE  index country_id_on_account_histories on account_histories(country_id);

CREATE index audio_id_on_audio_chapters on audio_chapters(audio_id);

CREATE index user_id_on_hi_coin_histories on hi_coin_histories(user_id);
CREATE index product_channel_id_on_hi_coin_histories on hi_coin_histories(product_channel_id);
CREATE index gift_order_id_on_hi_coin_histories on hi_coin_histories(gift_order_id);
CREATE index union_id_on_hi_coin_histories on hi_coin_histories(union_id);
CREATE index withdraw_history_id_on_hi_coin_histories on hi_coin_histories(withdraw_history_id);
CREATE index country_id_on_hi_coin_histories on hi_coin_histories(country_id);
CREATE index product_id_on_hi_coin_histories on hi_coin_histories(product_id);