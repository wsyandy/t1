// pages/my_focus/my_focus.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    /* 关注 */
    nofocus: false,
    icon_nofocus: '/images/icon_nofocus.png',
    ico_room: '/images/ico_room.png',
    ico_male: '/images/ico_male.png',
    ico_female: '/images/ico_female.png',
    be_fond: '/images/be-fond.png',
    focusList: [
      {
        userId: 134713,
        nickname: '孙悟空不要饭孙悟空不要饭孙悟空不要饭孙悟空不要饭',
        avatar: '/images/room_cover_1.jpg',
        signature: '这个家伙忙着连麦，一句话都没留下这个家伙忙着连麦，一句话都没留下',
        room: 0,
        distance: '附近',
        male: 1,
        age: 21,
        befond: false,
      },
      {
        userId: 124713,
         nickname: '小龙女',
        avatar: '/images/room_cover_2.jpg',
        signature: '这个家伙忙着连麦，一句话都没留下',
        room: 1,
        distance: '400m',
        male: 0,
        age: 22,
        befond: true,
      },
      {
        userId: 13513,
        nickname: '可爱的她',
        avatar: '/images/room_cover_1.jpg',
        signature: '这个家伙忙着连麦，一句话都没留下',
        room: 1,
        distance: '1.4km',
        male: 1,
        age: 23,
        befond: false,
      },
      {
        userId: 145673,
        nickname: '约那个啥',
        avatar: '/images/room_cover_2.jpg',
        signature: '这个家伙忙着连麦，一句话都没留下',
        room: 0,
        distance: '2.4km',
        male: 0,
        age: 24,
        befond: false,
      },
    ],

  },
  toBeFond:function(e){
    let i = e.currentTarget.dataset.index;
    let focusList = this.data.focusList;
   
    focusList[i].befond = !focusList[i].befond
    this.setData({
      focusList :focusList
    })
  },
  navtoUserInfo: function (e) { 
    let id = e.currentTarget.dataset.id;
    console.log(id)
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