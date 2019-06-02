const DB_CONFIG = require('../config/config_db');
const MONGOOSE = require('mongoose');

const connect = () => {
  //server: { socketOptions: { keepAlive: 300000, connectTimeoutMS: 30000 } },
  //replset: { socketOptions: { keepAlive: 300000, connectTimeoutMS : 30000 } }
  const options = {
    useNewUrlParser: true,
    keepAlive: 300000, 
    connectTimeoutMS: 30000
  };
  const state = MONGOOSE.connection.readyState;
  if(state === 0 || state === 3){
    if(MONGOOSE.connect(DB_CONFIG.db, options)){
      return true;
    }else{
      return false;
    }
  } else {
    return true;
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
