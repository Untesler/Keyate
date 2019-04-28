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
  let err;
  const user = (req.token !== null) ? AUTHENICATION.authenicate(req.token) : null;
  const work_uid = await getNextSeq('Illustrations');
  let uploadData = {
    uid: work_uid,
    name: req.body.name,
    path: 'illusts/',
    illustrator: user.uid,
    tag: [],
    category: [],
    release_date: new Date(Date.now),
    views: 0,
    populars: 0,
    deleted: false,
    comments_box_id: 'p'+ work_uid.toString()
  };

  if(user){
    if(req.body.tag !== '' && req.body.tag !== undefined) 
    {
      uploadData.tag = req.body.tag.replace(/ /g, '').split(',');
    }
    if(req.body.category !== '' && req.body.category !== undefined)
    {
      uploadData.category = req.body.category.replace(/ /g, '').split(',');
    }
    if(req.body.release_date !== '' && req.body.release_date !== undefined) uploadData.release_date = new Date(req.body.release_date);
    if(req.body.description !== '' && req.body.description !== undefined) uploadData.description = req.body.description;

    if(req.files !== undefined )
    {
      if(req.files.work.truncated === false)
      {
        if(req.files.work.size > 0){
          let fileData = req.files.work;
          const extension = (fileData.name).substring(
            (fileData.name).lastIndexOf('.'), 
            (fileData.name).length);
          const fileName = 'i_' + user.uid + extension;
          const newpath = PATH.join(__dirname, '../')+ "/assets/imgs/upload/illusts/"+fileName;
          uploadData.path += fileName;

          [ err ] = await to(fileData.mv(newpath));
          if(err){
            LOG.write('Error', 'Failed to create work image ('+err+').');
            FS.unlink(req.files.work.tempFilePath, (err) => {
              if(err) LOG.write('Error', 'Failed to delete tmp file ('+err+').');
            });
            return res.sendStatus(500);
          }
          uploadData.path = "illusts/" + fileName;
        }else{
          LOG.write('Error', 'Failed to upload data because size of req files <= 0');
          FS.unlink(req.files.work.tempFilePath, (err) => {
            if(err) LOG.write('Error', 'Failed to delete tmp file ('+err+').');
          });
          return res.sendStatus(406);
        }

        if(DBC.connect()){
          [ err ] = await to(MODEL.updateOne({uid: user.uid}, uploadData));
          if(err){
            LOG.write('Database', 'updateOne failed beacause ('+err+').');
            await decreaseSeq('Illusts');
            return res.sendStatus(503);
          }else{
            LOG.write('Database', 'Uid['+user.uid+'] Update profile success.')
          }
          return res.sendStatus(201); //TODO need to create comment box here too
        }else{
          await decreaseSeq('Illusts');
          return res.sendStatus(503);
        }

      }else{
        LOG.write('Error', 'Failed to upload data because file size limit has been reached.');
        await decreaseSeq('Illusts');
        return res.sendStatus(406);
      }

    }else{
      LOG.write('Error', 'Failed to submit work because image file not found.');
      await decreaseSeq('Illusts');
      return res.sendStatus(406);
    }
  }else{
    LOG.write('Authenicate', 'Failed to submit work because token not found.');
    await decreaseSeq('Illusts');
    return res.sendStatus(401);
  }
}

const deleteWork = async (req, res) =>{
  let err;
  const user = (req.token !== null) ? AUTHENICATION.authenicate(req.token) : null;
}

const getWork = async (req, res) =>{
  let err;
  const user = (req.token !== null) ? AUTHENICATION.authenicate(req.token) : null;
}

const addView = async (req, res) =>{
}

const addPopular = async (req, res) =>{
  let err;
  const user = (req.token !== null) ? AUTHENICATION.authenicate(req.token) : null;
}

async function getNextSeq(modelName){
  const seq = await COUNTER_MODEL.findById({ _id: modelName});
  await COUNTER_MODEL.updateOne({ _id: modelName }, { val: seq.val+1 });
  return seq.val;
}

async function decreaseSeq(modelName){
  const seq = await COUNTER_MODEL.findById({ _id: modelName});
  await COUNTER_MODEL.updateOne({ _id: modelName }, { val: seq.val-1 });
  return seq.val;
}

module.exports = {submitWork, deleteWork, getWork, addView, addPopular};
