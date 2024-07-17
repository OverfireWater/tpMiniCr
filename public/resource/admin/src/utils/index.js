import { cloneDeep } from 'lodash'

/**
 * Parse the time to string
 * @param {(Object|string|number)} time
 * @param {string} cFormat
 * @returns {string | null}
 */
export function parseTime(time, cFormat) {
  if (arguments.length === 0 || !time) {
    return null
  }
  const format = cFormat || '{y}-{m}-{d} {h}:{i}:{s}'
  let date
  if (typeof time === 'object') {
    date = time
  } else {
    if ((typeof time === 'string')) {
      if ((/^[0-9]+$/.test(time))) {
        // support "1548221490638"
        time = parseInt(time)
      } else {
        // support safari
        // https://stackoverflow.com/questions/4310953/invalid-date-in-safari
        time = time.replace(new RegExp(/-/gm), '/')
      }
    }

    if ((typeof time === 'number') && (time.toString().length === 10)) {
      time = time * 1000
    }
    date = new Date(time)
  }
  const formatObj = {
    y: date.getFullYear(),
    m: date.getMonth() + 1,
    d: date.getDate(),
    h: date.getHours(),
    i: date.getMinutes(),
    s: date.getSeconds(),
    a: date.getDay()
  }
  const time_str = format.replace(/{([ymdhisa])+}/g, (result, key) => {
    const value = formatObj[key]
    // Note: getDay() returns 0 on Sunday
    if (key === 'a') { return ['日', '一', '二', '三', '四', '五', '六'][value ] }
    return value.toString().padStart(2, '0')
  })
  return time_str
}

/**
 * @param {number} time
 * @param {string} option
 * @returns {string}
 */
export function formatTime(time, option) {
  if (('' + time).length === 10) {
    time = parseInt(time) * 1000
  } else {
    time = +time
  }
  const d = new Date(time)
  const now = Date.now()

  const diff = (now - d) / 1000

  if (diff < 30) {
    return '刚刚'
  } else if (diff < 3600) {
    // less 1 hour
    return Math.ceil(diff / 60) + '分钟前'
  } else if (diff < 3600 * 24) {
    return Math.ceil(diff / 3600) + '小时前'
  } else if (diff < 3600 * 24 * 2) {
    return '1天前'
  }
  if (option) {
    return parseTime(time, option)
  } else {
    return (
      d.getMonth() +
      1 +
      '月' +
      d.getDate() +
      '日' +
      d.getHours() +
      '时' +
      d.getMinutes() +
      '分'
    )
  }
}

/**
 * @param {string} url
 * @returns {Object}
 */
export function param2Obj(url) {
  const search = decodeURIComponent(url.split('?')[1]).replace(/\+/g, ' ')
  if (!search) {
    return {}
  }
  const obj = {}
  const searchArr = search.split('&')
  searchArr.forEach(v => {
    const index = v.indexOf('=')
    if (index !== -1) {
      const name = v.substring(0, index)
      obj[name] = v.substring(index + 1, v.length)
    }
  })
  return obj
}
// 路由操作
export function getMenuOpen(to, menuList) {
  const allMenus = []
  menuList.forEach((menu) => {
    const menus = transMenu(menu, [])
    allMenus.push({
      path: menu.path,
      openNames: []
    })
    menus.forEach((item) => allMenus.push(item))
  })
  const currentMenu = allMenus.find((item) => item.path === to.path)
  return currentMenu ? currentMenu.openNames : []
}
// 路由操作
function transMenu(menu, openNames) {
  if (menu.children && menu.children.length) {
    const itemOpenNames = openNames.concat([menu.path])
    return menu.children.reduce((all, item) => {
      all.push({
        path: item.path,
        openNames: itemOpenNames
      })
      const foundChildren = transMenu(item, itemOpenNames)
      return all.concat(foundChildren)
    }, [])
  } else {
    return [menu].map((item) => {
      return {
        path: item.path,
        openNames: openNames
      }
    })
  }
}

// 获取当前菜单对象
export const getCurrentMenu = (menuList, newOpenMenus) => {
  menuList.forEach((item) => {
    const newMenu = {}
    for (const i in item) {
      if (i !== 'children') newMenu[i] = cloneDeep(item[i])
    }
    newOpenMenus.push(newMenu)
    item.children && getCurrentMenu(item.children, newOpenMenus)
  })
  return newOpenMenus
}

/**
 * 将字符串转换为驼峰
 */
export const toCamelCase = (str) => {
  // 使用正则表达式匹配所有下划线及其后面的字符，并使用一个回调函数来处理这些匹配
  return str.replace(/_([a-z])/g, function(g) {
    // 回调函数接收整个匹配项（这里是下划线及其后的字符），通过取匹配项中的第二个字符（即下划线后的字符）
    // 并将其转换为大写来替换整个匹配项
    return g[1].toUpperCase()
  }).replace(/^./, function(match) {
    // 将字符串的第一个字符也转换为大写（如果它是小写的话）
    // 注意：这假设原始字符串以小写字母开头，且你想要它变成大写
    return match.toUpperCase()
  })
}
