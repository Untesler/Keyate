const MONGOOSE = require('mongoose');
const isEmail = require('validator/lib/isEmail');
const timestamp = Date.now();

const USERSCHEMA = MONGOOSE.Schema({

  uid : {type: Number, required: true, unique: true},
  penname : {
    type: String, 
    unique: true, 
    required: true, 
    trim: true
  },
  email : {
    type: String, 
    required: true, 
    unique: true, 
    trim: true, 
    lowercase: true,
    validate: [{validator: value => isEmail(value), msg: 'Invalid email.'}]
  },
  password : {type: String, required: true},
  gender : {type: Number, required: false, default: -1},
  birthdate : {type: Date, required: false, default: new Date(timestamp)},
  description : {type: String, required: false, default: 'Welcome to my profile.'},
  followers : [ Number ],
  following : [ Number ],
  favorites : [ Number ],
  avatar : {type: String, required: false, default: 'avatars/default.png'}

}, { collection : 'Users'}

);

module.exports = MONGOOSE.model('User', USERSCHEMA);
