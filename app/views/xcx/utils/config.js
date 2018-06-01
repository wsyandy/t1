var debug = 2;   //0：线上  1：线下  2：本地
var config = {
  code: '',
  model: ''
};

switch (debug) {
  case 0:
    config.server_domain = ""
    config.websocket_domain = ""
    config.image_url = ""
    break;
  case 1:
    config.server_domain = "http://test.xianjinchaoren.com/"
    config.websocket_domain = ""
    config.image_url = "http://test.xianjinchaoren.com/xcx/images/"
    break;
  case 2:
    config.server_domain = "http://chance.com/"
    config.websocket_domain = "ws://127.0.0.1:9501"
    config.image_url = "http://chance.com/xcx/images/"
    break;
}

module.exports = {
  config: config,
  debug: debug,
  version_number: "1.0.0"
};