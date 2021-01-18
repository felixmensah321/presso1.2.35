import axios from 'axios'
import APICONFIG from '../static/api/config.json'

export default axios.create({
  baseURL: APICONFIG.path,
  headers: {
    'Authorization': 'Bearer ' + APICONFIG.bearer,
    'Content-Type': 'application/json'
  }
})
