/* eslint no-unused-vars: 0 */
const EXPRESS = require('express');
const DBC = require('../controllers/MongooseConnect');
const MODEL = require('../models/IllustrationModel');
const COUNTER_MODEL = require('../models/CounterModel');
const BCRYPT = require('bcrypt');
const PATH = require('path');
const FS = require('fs');
const LOG = require('../assets/srcs/Log');
const AUTHENICATION = require('../controllers/Authenication');
const to = require('await-to-js').default;
const PRIVATE_KEY = FS.readFileSync(PATH.resolve(__dirname, '../config/private.key'));

const submitWork = async (req, res) =>{
}

const deleteWork = async (req, res) =>{
}

const getWork = async (req, res) =>{
}

const addView = async (req, res) =>{
}

const addPopular = async (req, res) =>{
}

async function getNextSeq(modelName){
  const seq = await COUNTER_MODEL.findById({ _id: modelName});
  await COUNTER_MODEL.updateOne({ _id: modelName }, { val: seq.val+1 });
  return seq.val;
}

module.exports = {submitWork, deleteWork, getWork, addView, addPopular};
