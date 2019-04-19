const MONGOOSE = require('mongoose');

const USERSCHEMA = MONGOOSE.Schema({

  firstname : {type: String, required: true},
  lastname : {type: String, required: true},
  avartar : {type: String, required: true}

}, { collection : 'test'}

);

module.exports = MONGOOSE.model('User', USERSCHEMA);
