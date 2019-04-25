const DBC = require('../controllers/MongooseConnect');
const MODEL = require('../models/UserModel');
const JWT = require('jsonwebtoken');
const PATH = require('path');
const FS = require('fs');
const PRIVATE_KEY = FS.readFileSync(PATH.resolve(__dirname, '../config/private.key'));
const LOG = require('../assets/srcs/Log');

const authenicate = (token) => {
  const decode = JWT.verify(token, PRIVATE_KEY, (err, decoded) => {
    if(err) {
      LOG.write('Authenicate', "Verify token failed because ("+err+").");
      return false;
    }
    else
    {
      if(DBC.connect()){
        if(MODEL.findOne({uid: decoded.uid, email: decoded.email}, (err, result) =>{
          if(err) {
            LOG.write('Database', "findOne failed because ("+err+").");
            return false;
          }
          else{
            if(result){
              LOG.write('Authenicate', "Uid[" + decoded.uid + "] accept access.");
              return true;
            }
            else {
              LOG.write('Authenicate', "Malicious attempt to loggen in is detected");
              return false;
            }
          }
        })) return decoded;
      }else{
        LOG.write('Connection', "Can't make a connection ( authenicate ).");
        return false;
      }
    }
  });
  return decode;
}

module.exports = {authenicate};
