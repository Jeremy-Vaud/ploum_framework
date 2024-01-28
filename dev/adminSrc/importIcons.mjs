import data from './data.json' assert { type: "json" }
import { writeFile } from 'fs'

let part1 = ""
let part2 =  "\nexport default {"
data.pages.forEach((e) => {
    part1 += `import { ${e.icon} } from '@fortawesome/free-solid-svg-icons'\n`
    part2 += `'${e.icon}':${e.icon},`
})
part2 = part2.slice(0,-1) + "}"

writeFile('./dev/adminSrc/icons.js', part1+part2, err => {
    if (err) {
        console.error(err)
    } else {
        console.log("Fichier icons.js crée")
    }

});