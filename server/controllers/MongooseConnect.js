const DB_CONFIG = require('../config/config_db');
const MONGOOSE = require('mongoose');

const connection = () => {
  let state = MONGOOSE.connection.readyState;
  if(state === 0 || state === 3){
    if(MONGOOSE.connect(DB_CONFIG.db, {useNewUrlParser: true})){
      return true;
    }else{
      return false;
    }
  }
  return true;
}

const disconnect = () => {
  let state = MONGOOSE.connection.readyState;
  if(state === 1 || state === 2){
    MONGOOSE.connection.close();
  }
  return true;
}

module.exports = {MONGOOSE, connection, disconnect};
