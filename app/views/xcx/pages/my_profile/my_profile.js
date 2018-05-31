// pages/my_profile/my_profile.js
var tcity = require("../../utils/citys.js");
var configs = require("../../utils/config.js");
var province = '北京';
var city = '北京市';
var oldprovince = '';
var oldcity = '';
Page({

  /**
   * 页面的初始数据
   */
  data: {
    profile_arrow: '/images/profile_arrow.png',
    icoClose: '/images/ico_close.png',
    userInfo: {
      avatar: '/images/room_cover_1.jpg',
      id: "189069",
      nickname: "秦王",
      sex: '女',
      province: '上海',
      city: '上海',
      birthday: '1995-11-11'
    },
    hideMask: true, /*遮罩层*/
    hideNick: true,  /*修改昵称*/
    nicknameVal: '',
    hideSex: true,  /*修改性别*/
    sexOpt:["男","女","取消"],
    /*城市选择*/
    provinces: [],
    citys: [],
    province: '北京',
    city: '北京',
    value: [0, 0, 0],
    values: [0, 0, 0],
    condition: false,
    birthday:'1995-11-11',
  },
  /*打开昵称编辑弹窗*/
  editNickname: function () {
    this.setData({
      hideMask: false,
      hideNick: false
    })
  },
  /*监听昵称输入框*/
  bindNicknameInput: function (e) {
    this.setData({
      nicknameVal: e.detail.value
    })
  },
  /*保存昵称 并关闭昵称编辑弹窗*/
  saveNickname: function () {
    let userInfo = this.data.userInfo;
    let nicknameVal = this.data.nicknameVal;
    userInfo.nickname = nicknameVal ? nicknameVal : userInfo.nickname
    this.setData({
      hideMask: true,
      hideNick: true,
      userInfo: userInfo
    })

  },
  /*关闭昵称编辑弹窗*/
  closeMask: function () {
    this.setData({
      hideMask: true,
      hideSex: true,
    })
  },

  /*打开性别选择弹窗*/
  editSex: function () { 
    this.setData({
      hideMask: false,
      hideSex: false,
    })
  },
  sexSelect: function (e) {
    let index = e.currentTarget.dataset.index;
    let userInfo = this.data.userInfo; 
  
    if(index<2){
      userInfo.sex = this.data.sexOpt[index];
      this.setData({ 
        userInfo: userInfo
      })
    } 
    this.setData({
      hideMask: true,
      hideSex: true,
    })
   
  },
  /*打开地区   选择弹窗*/
  open: function (e) {
    var bool = Number(e.currentTarget.dataset.bool);
    console.log(bool)
    // console.log(oldprovince, oldcity)
    if (bool) {
      this.setData({
        province: oldprovince,
        city: oldcity,
       
      })
      province = oldprovince
      city = oldcity
    } else {
      oldprovince = this.data.province;
      oldcity = this.data.city
    }

    this.setData({
      hideMask: !this.data.hideMask,
      condition: !this.data.condition
    })

  },
  bindChange: function (e) {
    var val = e.detail.value
    var t = this.data.values;
    var cityData = this.data.cityData;

    if (val[0] != t[0]) {
      const citys = [];

      for (let i = 0; i < cityData[val[0]].sub.length; i++) {
        citys.push(cityData[val[0]].sub[i].name)
      }

      this.setData({
        province: this.data.provinces[val[0]],
        city: cityData[val[0]].sub[0].name,
        citys: citys,
        values: val,
        value: [val[0], 0, 0]
      })
      province = this.data.provinces[val[0]]
      city = cityData[val[0]].sub[0].name

      return;
    }
    if (val[1] != t[1]) {
      this.setData({
        city: this.data.citys[val[1]],
        values: val,
        value: [val[0], val[1], 0]
      })
      city = this.data.citys[val[1]]
      return;
    }

  },
/*生日*/
  bindDateChange: function (e) {
    console.log('picker发送选择改变，携带值为', e.detail.value)
    this.setData({
      birthday: e.detail.value
    })
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

    // 初始化城市
    var _this = this;
    tcity.init(_this);
    var cityData = _this.data.cityData; 
    const provinces = [];
    const citys = [];

    for (let i = 0; i < cityData.length; i++) {
      provinces.push(cityData[i].name);
    }
    for (let i = 0; i < cityData[0].sub.length; i++) {
      citys.push(cityData[0].sub[i].name)
    }
    _this.setData({
      'provinces': provinces,
      'citys': citys,
      'province': cityData[0].name,
      'city': cityData[0].sub[0].name,
    })
    console.log('初始化完成');
    

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})