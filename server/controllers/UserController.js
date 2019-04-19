const EXPRESS = require('express');
const DBC = require('../controllers/MongooseConnect');
const MODEL = require('../models/UserModel');

const getIndexPage = (req, res) => {
  res.send('This is user page');
};

const getData = async (req, res) => {
  if(DBC.connection()){
    res.send(await MODEL.find({}));
  }else{
    res.send("Can't make a connection.");
  }
  DBC.disconnect();
};

module.exports = {getIndexPage, getData};
