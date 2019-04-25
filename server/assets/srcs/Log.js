const PATH = require('path');
const FS = require('fs');

const write = (type, message) => {
  const timestamp = new Date(Date.now());
  const fileName = `log-[${timestamp.getDate()}.${timestamp.getMonth()}.${timestamp.getFullYear()}].txt`;
  const log = `[${type}] ${timestamp.toLocaleString('en-EN', { timeZone: 'Asia/Bangkok'})} : ${message}\n`;

  FS.appendFile(PATH.resolve(__dirname, '../logs/'+fileName), log, (err)=>{
    if(err) console.log(err);
  });
}

module.exports = {write};
