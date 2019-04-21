const MONGOOSE = require('mongoose');

const COMMENTSCHEMA = MONGOOSE.Schema({

  id : {type: String, required: true},
  comment : {
    commentator : {type: Number, required: true},
    helpgul : {type: Number, default: 0},
    comment : {type: String, required: true},
    deleted : {type: Boolean, default: false}
  } 

}, { collection : 'Comments'}

);

module.exports = MONGOOSE.model('Comment', COMMENTSCHEMA);
