const dateFormat = require('dateformat')

export default {
	methods: {
		beautyTime(time) {
			const subtime = new Date().getTime() - time * 1000
			if (subtime <= 1000 * 60) {
				return t('comicmode', '{num} seconds ago', { num: Math.ceil(subtime / 1000) })
			} else if (subtime <= 1000 * 60 * 60) {
				return t('comicmode', '{num} minutes ago', { num: Math.ceil(subtime / (1000 * 60)) })
			} else if (subtime <= 1000 * 60 * 60 * 24) {
				return t('comicmode', '{num} hours ago', { num: Math.ceil(subtime / (1000 * 60 * 60)) })
			} else if (subtime <= 1000 * 60 * 60 * 24 * 7) {
				return t('comicmode', '{num} days ago', { num: Math.ceil(subtime / (1000 * 60 * 60 * 24)) })
			} else {
				return dateFormat(new Date(time * 1000), 'yyyy-dd-mm HH:MM:ss')
			}

		},
	},
}
