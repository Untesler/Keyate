const DB_CONFIG = require('../config/config_db');
const MONGOOSE = require('mongoose');

const connect = () => {
  if(MONGOOSE.connect(DB_CONFIG.db, {useNewUrlParser: true})){
    return true;
  }else{
    return false;
  }
}

const disconnect = () => {
  MONGOOSE.connection.close();
  return true;
}

const state = () => {
  return MONGOOSE.connection.readyState;
}

module.exports = {MONGOOSE, connect, disconnect, state};
