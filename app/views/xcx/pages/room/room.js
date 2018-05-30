// pages/room/room.js
Page({

  /**
   * 页面的初始数据
   */
  data: { 
    roomBg: '/images/room_bg1.png',
    ico_cup: '/images/ico_cup.png',
    arrowRight: '/images/arrow_right.png',
    icoSpeaker: '/images/ico_speaker.png',
    roomInfo:{
      room_name:'休闲听音乐',
      room_number: 10,
      homeowners: '/images/room_cover_1.jpg',
      nickname: '女神~经',
      signature: '我应在江湖悠悠饮一壶浊酒',
    },
    /*座位列表*/
    icoSofa: '/images/ico_sofa.png',
    icoProhibit: '/images/ico_prohibit.png',
    seatList:[
      {
        avatar: '',
        nickname: '',
        vacancy: true,
        prohibit: false,
        seal: false,
        phonate: false
      },
      {
        avatar: '',
        nickname: '',
        vacancy: true,
        prohibit: false,
        seal: false,
        phonate: false
      },
      {
        avatar: '/images/room_cover_1.jpg',
        nickname:'女神1号',
        vacancy: false,
        prohibit: false,
        seal:false,
        phonate: false
      },
      {
        avatar: '/images/room_cover_2.jpg',
        nickname: '女神12号',
        vacancy: false,
        prohibit: false,
        seal: false,
        phonate: true
      },
      {
        avatar: '/images/room_cover_1.jpg',
        nickname: '女神12号',
        vacancy: false,
        prohibit: false,
        seal: false,
        phonate: true
      },
      {
        avatar: '',
        nickname: '',
        vacancy: false,
        prohibit: false,
        seal: true,
        phonate: false
      },
      {
        avatar: '/images/room_cover_1.jpg',
        nickname: '女神1号',
        vacancy: false,
        prohibit: true,
        seal: false,
        phonate: false
      },
      {
        avatar: '',
        nickname: '',
        vacancy: false,
        prohibit: false,
        seal: true,
        phonate: false
      },
    ],
    tabbar:[
      {
        icon: '/images/bar_speaker.png',
        prohibit: '/images/bar_speaker_prohibit.png',
      },
      {
        icon: '/images/bar_microphone_prohibit.png',
        prohibit: '/images/bar_microphone.png',
      },
      {
        icon: '/images/bar_gift.png', 
      },
      {
        icon: '/images/bar_write.png',
      },
      {
        icon: '/images/bar_share.png',
      }
    ],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
  
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