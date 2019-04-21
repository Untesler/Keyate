const MONGOOSE = require('mongoose');
const isEmail = require('validator/lib/isEmail');

const USERSCHEMA = MONGOOSE.Schema({

  uid : {type: Number, required: true},
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
  gender : {type: Number, required: false},
  birthdate : {type: Date, required: false, default: Date.now()},
  description : {type: String, required: false, default: 'Welcome to my profile.'},
  follower : {type: Number, required: false, default: 0},
  avatar : {type: String, required: false, default: 'assets/imgs/upload/avatars/default.png'}

}, { collection : 'Users'}

);

module.exports = MONGOOSE.model('User', USERSCHEMA);
