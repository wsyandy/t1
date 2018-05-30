const configs = require("config.js");
const formatTime = date => {
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()
  const hour = date.getHours()
  const minute = date.getMinutes()
  const second = date.getSeconds()

  return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

const formatNumber = n => {
  n = n.toString()
  return n[1] ? n : '0' + n
}

const log = (msg, level) => {
  let time = formatTime(new Date());
  if (configs.debug == 0) {
    return;
  }
  if (level === "error") {
    console.error(`[INFO]${time}: ${msg}`);
  } else {
    console.log(`[INFO]${time}: ${msg}`);
  }
}

const conversion = Numbers => {
  if (Numbers >= 10000) {
    Numbers = (Numbers / 10000) + '万'
  }
  // else if (Numbers >= 1000) {
  //   Numbers = (Numbers / 1000) + '千'
  // }
  return Numbers
};


module.exports = {
  formatTime: formatTime,
  log: log,
  conversion: conversion
}
