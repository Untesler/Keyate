/* eslint no-unused-vars: 0 */
const EXPRESS = require('express');
const DBC = require('../controllers/MongooseConnect');
const MODEL = require('../models/UserModel');
const COUNTER_MODEL = require('../models/CounterModel');
const JWT = require('jsonwebtoken');
const BCRYPT = require('bcrypt');
const PATH = require('path');
const FS = require('fs');
const LOG = require('../assets/srcs/Log');
const AUTHENICATION = require('../controllers/Authenication');
const PRIVATE_KEY = FS.readFileSync(PATH.resolve(__dirname, '../config/private.key'));

const getData = async (req, res) => {
  if(DBC.connect()){
    res.send(await MODEL.find({}));
  }else{
    res.send("Can't make a connection.");
  }
  DBC.disconnect();
};

const register = async (req, res) => {
  if(DBC.connect()){
    req.body.password = BCRYPT.hashSync(req.body.password, 10);
    const data = {
      uid : await getNextSeq('Users'),
      penname : req.body.penname,
      email : req.body.email,
      password : req.body.password,
      gender : undefined,
      birthdate : undefined,
      description : undefined,
      follower : undefined,
      avatar : undefined
    };

    MODEL.create(data, (err) =>{
      if(err){ 
        let status = '';
        if((err.message).search('keyate.Users index: email') !== -1) status = 'Duplicate email';
        else if((err.message).search('keyate.Users index: penname') !== -1) status = 'Duplicate penname';
        res.json({'status': status}) 
        DBC.disconnect();
      }
      else {
        res.json({status: 'Registered'});
        DBC.disconnect();
      }
     });

  }else{
    res.send("Can't make a connection.");
  }
};

const signIn = async (req, res) => {
  if(DBC.connect()){
    await MODEL.findOne(
      {email: req.body.email},
      (err, user) =>{
        if(err) {res.send(err);}
        else{
          if(user){
            if(BCRYPT.compareSync(req.body.password, user.password)){
              const payload = {
                uid: user.uid,
                email: user.email
              }
              let token = JWT.sign(payload, PRIVATE_KEY,{
                expiresIn: '24h'
              })
              res.send(token);
            }
            else res.json({ status: 'Password mismatch.'});
          }
          else res.json({ status: 'User does not exist.'});
        }
    });
    DBC.disconnect();
  }else{
    res.send("Can't make a connection.");
    DBC.disconnect();
  }
};

const setProfile = async (req, res) => {
  const user = AUTHENICATION.authenicate(req.body.token);
  let updateData = new Object();

  if(user){

    if(req.body.penname !== '') updateData.penname = req.body.penname;
    if(req.body.password !== '') updateData.password = BCRYPT.hashSync(req.body.password, 10);
    if(req.body.gender !== '') updateData.gender = req.body.gender;
    if(req.body.birthdate !== '') updateData.birthdate = new Date(req.body.birthdate);
    if(req.body.description !== '') updateData.description = req.body.description;

    if(req.files.avatar.truncated === false) 
    {
      if(Object.keys(req.files).length !== 0){
        if(req.files.avatar.size !== 0){
          let fileData = req.files.avatar;
          const extension = (fileData.name).substring(
            (fileData.name).lastIndexOf('.'), 
            (fileData.name).length);
          const fileName = 'ava_' + user.uid + extension;
          const newpath = PATH.join(__dirname, '../')+ "/assets/imgs/upload/avatars/"+fileName;
          const old_avatar = await MODEL.findOne({uid: user.uid}, 'avatar', (err, result)=>{
            if(err){
              LOG.write('Database', 'findOne failed because ('+err+').');
              res.sendStatus(503);
            }else return result;
          });

          if(old_avatar.avatar != 'assets/imgs/upload/avatars/default.png'){
            await FS.unlink(PATH.resolve(__dirname, '../'+old_avatar.avatar), (err) =>{
              if(err){
                LOG.write('Error', 'Failed to delete old avatar ('+err+').');
                res.sendStatus(500);
              }
            });
          }

          fileData.mv(newpath, (err) =>{
            if(err) {
              LOG.write('Error', 'Failed to create avatar image ('+err+').');
              FS.unlink(req.files.avatar.tempFilePath, (err) => {
                if(err) LOG.write('Error', 'Failed to delete tmp file ('+err+').');
              });
              return res.sendStatus(500);
            }
          });
          updateData.avatar = "assets/imgs/upload/avatars/" + fileName;
        }else{
          FS.unlink(req.files.avatar.tempFilePath, (err) => {
            if(err) LOG.write('Error', 'Failed to delete tmp file ('+err+').');
          });
        }
      }else res.sendStatus(400);
    }

    await MODEL.updateOne({uid: user.uid}, updateData, (err) =>{
      if(err){
        LOG.write('Database', 'updateOne failed beacause ('+err+').');
        res.sendStatus(503);
      }else{
        LOG.write('Database', 'Uid['+user.uid+'] Update profile success.')
      }
    });
    res.sendStatus(201);
  }else{
    FS.unlink(req.files.avatar.tempFilePath, (err) => {
      if(err) LOG.write('Error', 'Failed to delete tmp file ('+err+').');
    });
    res.sendStatus(401);
  }
};

const getDecode = (req, res) => {
  const decoded = JWT.verify(req.body.token, PRIVATE_KEY);
  res.send(decoded.email);
}

async function getNextSeq(modelName){
  const seq = await COUNTER_MODEL.findById({ _id: modelName});
  await COUNTER_MODEL.updateOne({ _id: modelName }, { val: seq.val+1 });
  return seq.val;
}


module.exports = {getData, register, signIn, getDecode, setProfile};
