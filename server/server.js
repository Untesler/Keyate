const EXPRESS = require('express');
const BODYPARSER = require('body-parser');
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

SERVER.use('/users', USERS);

//if have no any route to handle this req or req.url are index or default
SERVER.get('*', (req, res) =>{
  res.setHeader('Content-Type', 'text/html');
  if(req.url === '/' || req.url === '/index' || req.url === '/index/'){
    res.end('<b> This is Index </b>');
  }else{
    res.sendStatus(404);
  }
});

SERVER.listen(PORT, ()=> console.log('Server started on port : ' + PORT));
