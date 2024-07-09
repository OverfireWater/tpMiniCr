import axios from 'axios'
import { MessageBox, Message } from 'element-ui'
import store from '@/store'

const base_api = process.env.VUE_APP_BASE_API
const base_url = process.env.VUE_APP_BASE_URL

// create an axios instance
const service = axios.create({
  baseURL: base_url + base_api, // url = base url + request url
  // withCredentials: true, // send cookies when cross-domain requests
  timeout: 5000 // request timeout
})

// 请求拦截器
service.interceptors.request.use(
  config => {
    // do something before request is sent
    const token = store.getters.token
    if (token) {
      config.headers['Authorization'] = token
    }
    return config
  },
  error => {
    // do something with request error
    console.log(error) // for debug
    return Promise.reject(error)
  }
)

// response interceptor
service.interceptors.response.use(
  response => {
    const res = response.data
    if (res.status !== 200) {
      Message({
        message: res.msg || 'Error',
        type: 'error',
        duration: 5 * 1000
      })

      // 50008: Illegal token; 50012: Other clients logged in; 50014: Token expired;
      if (res.status === 110003) {
        // to re-login
        MessageBox.alert(res.msg, {
          confirmButtonText: '确定',
          type: 'warning'
        }).then(() => {
          store.dispatch('user/resetToken').then(() => {
            location.reload()
          })
        })
      }
      return Promise.reject(new Error(res.message || 'Error'))
    } else {
      return res
    }
  },
  error => {
    console.log('err' + error) // for debug
    Message({
      message: error.message,
      type: 'error',
      duration: 5 * 1000
    })
    return Promise.reject(error)
  }
)

export default service
