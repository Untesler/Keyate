const MONGOOSE = require('mongoose');

const ILLUSTRATIONSCHEMA = MONGOOSE.Schema({

  name : {type: String, required: true},
  illustrator : {type: Number, required: true},
  description : {type: String, default: ''},
  tag : [ String ],
  category : [ String ],
  release_date : {type: Date, default: Date.now},
  views : {type: Number, default: 0},
  populars : {type: Number, default: 0},
  deleted : {type: Boolean, default: false},
  comments_box_id : {type: String, required: true}

}, { collection : 'Illustrations'}

);

module.exports = MONGOOSE.model('Illustration', ILLUSTRATIONSCHEMA);
