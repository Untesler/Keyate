const MONGOOSE = require('mongoose');

const COMMENTSCHEMA = MONGOOSE.Schema({

  id : {
    type    : String,
    required: true,
    trim    : true,
    unique  : true
  },
  comment : {
    commentator: {type: Number, required: true},
    helpful    : {type: Number, required: true, default: 0},
    comment    : {type: String, required: true},
    deleted    : {type: Boolean, required: true, default: false},
    date       : {type: Date, required: true, default: new Date(Date.now())}
  } 

}, { collection : 'Comments'}

);

module.exports = MONGOOSE.model('Comment', COMMENTSCHEMA);
