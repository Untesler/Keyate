const EXPRESS = require('express');
const BODYPARSER = require('body-parser');
const FILEUPLOAD = require('express-fileupload');
const CORS = require('cors');

const SERVER = EXPRESS();
const PORT = process.env.PORT || 3000;
process.env.SECRET_KEY = 'I6&X3#fcq';

// Routes
const USERS = require('./routes/users');

// Middleware
SERVER.use(BODYPARSER.urlencoded({ extended: false }));
SERVER.use(BODYPARSER.json());
SERVER.use(CORS());
SERVER.use(
  FILEUPLOAD({
    limits: { fileSize: 5 * 1024 * 1024 /* 5MB */ },
    abortOnLimit: true,
    limitHandler: (req, res, next) =>{
      return res.json({'status': 'File size limit has been reached'});
    },
    useTempFiles : true,
    tempFileDir : __dirname + '/assets/tmp/'
}));

SERVER.use('/users', USERS);

//if have no any route to handle this req or req.url are index or default
SERVER.get('*', (req, res) =>{
  res.setHeader('Content-Type', 'text/html');
  if(req.url === '/' || req.url === '/index' || req.url === '/index/'){
    res.sendFile(__dirname + '/test/avatar_upload.html');
  }else{
    res.sendStatus(404);
  }
});

SERVER.listen(PORT, ()=> console.log('Server started on port : ' + PORT));
