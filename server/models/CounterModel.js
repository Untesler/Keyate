const MONGOOSE = require('mongoose');

const COUNTERSCHEMA = MONGOOSE.Schema({

  _id : {type: String, required: true},
  val : {type: Number, require: true, default: 0}

}, { collection : 'Counters', _id : false}

);

module.exports = MONGOOSE.model('Counter', COUNTERSCHEMA);
