const MONGOOSE = require('mongoose');

const COUNTERSCHEMA = MONGOOSE.Schema({

  _id : {type: String, required: true},
  val : {type: Number, require: true, default: 0}

}, { collection : 'Counters', _id : false}

);

module.exports = MONGOOSE.model('Counter', COUNTERSCHEMA);

/*
*
* Please insert the documents manually, used for do auto increment in mongo
* First: _id: Users, val: 0
* Second: _id: Illusts, val: 0
*
*/
