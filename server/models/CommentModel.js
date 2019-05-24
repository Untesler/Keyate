const MONGOOSE = require('mongoose');
const timestamp = Date.now();

const COMMENTSCHEMA = MONGOOSE.Schema({

  id : {
    type    : String,
    required: true,
    trim    : true,
    unique  : true
  },
  comments : {
    commentator: {type: Number, required: true, default: 0},
    helpful    : {type: Number, required: true, default: 0},
    comment    : {type: String, required: true, default: "empty"},
    deleted    : {type: Boolean, required: true, default: false},
    date       : {type: Date, required: true, default: new Date(timestamp)}
  }

}, { collection : 'Comments'}

);

module.exports = MONGOOSE.model('Comment', COMMENTSCHEMA);
