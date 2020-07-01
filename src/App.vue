<template>
	<div class="page">
		<div class="title">
			{{ title }}
		</div>
		<div class="imglist">
			<div v-for="img in imglist" :key="img.name">
				<img :id="img.name"
					class="img"
					:style="{'max-width':imgMaxWidth}"
					:origin-src="img.url">
			</div>
			<div class="chapters">
				<div v-if="previousChapterUrl" class="chapterbox textleft">
					<a class="link" href="javascript:void(0)" @click="gotoPreviousChapter()">{{ previousChapter }}</a>
				</div>
				<div v-if="nextChapterUrl" class="chapterbox textright">
					<a class="link" href="javascript:void(0)" @click="gotoNextChapter()">{{ nextChapter }}</a>
				</div>
			</div>
		</div>
		<div class="right-bottom">
			<div class="icon white-icon rotate180" @click="up()">
				<img :src="icons.download">
			</div>
			<div class="icon white-icon gt800px" @click="fullscreen()">
				<img :src="icons.fullscreen">
			</div>
			<div class="icon white-icon" @click="back()">
				<img :src="icons.close">
			</div>
		</div>
	</div>
</template>
<script>
import axios from '@nextcloud/axios'
import ImgLazyProcessor from 'img-lazy-processor'

export default {
	name: 'App',
	data() {
		return {
			dir: '',
			previousChapter: '',
			nextChapter: '',
			previousChapterUrl: '',
			nextChapterUrl: '',
			imgMaxWidth: '600px',
			icons: {
				close: '/custom_apps/comicmode/img/close-white.png',
				download: '/custom_apps/comicmode/img/download-white.png',
				fullscreen: '/custom_apps/comicmode/img/fullscreen-white.png',
			},
			imglist: [

			],
		}
	},
	computed: {
		parentDir() {
			return this.dir.substring(0, this.dir.lastIndexOf('/'))
		},
		title() {
			return this.dir.substring(this.dir.lastIndexOf('/') + 1)
		},
	},
	mounted() {
		this.init()
	},
	methods: {
		async init() {
			this.dir = document.getElementsByName('dir')[0].value
			if (this.dir.endsWith('/')) {
				this.dir = this.dir.substring(0, this.dir.length - 1)
			}
			document.title = this.title + '-' + document.title
			const resp = await axios.get(OC.generateUrl('/apps/comicmode/api/1.0/list.json?dir=' + this.dir))
			const data = resp.data
			const status = data.status

			if (status === 'OK') {
				let files = data.files
				files = files.sort(this.sort)
				this.imglist = files.map(f => {
					return {
						name: f.name,
						url: OC.generateUrl('/remote.php/webdav/' + this.dir + '/' + f.name),
					}
				})
				const imgLazyProcessor = new ImgLazyProcessor({
					dataset: true,
					bgColor: '#aec2d3',
					bgSize: '40%',
					bgPosition: 'center',
					bgRepeat: 'no-repeat',
					delay: false,
					delayTime: 300,
					disable: false,
				})
				setTimeout(() => {
					this.imglist.forEach(img => {
						const imgEle = document.getElementById(img.name)
					 imgLazyProcessor.observe(imgEle)
					})
					// 设置上下章
					let brothers = data.brothers
					if (brothers.length > 0) {
						brothers = brothers.sort(this.sort)
						let currentIndex = -1
						for (let i = 0; i < brothers.length; i++) {
							if (brothers[i].name === this.title) {
								currentIndex = i
							}
						}
						if (currentIndex !== 0) {
							const name = brothers[currentIndex - 1].name
							this.previousChapter = t('comicmode', 'previous chapter') + ': ' + name
							this.previousChapterUrl = OC.generateUrl('/apps/comicmode/?dir=' + this.parentDir + '/' + name)
						}
						if (currentIndex !== brothers.length - 1) {
							const name = brothers[currentIndex + 1].name
							this.nextChapter = t('comicmode', 'next chapter') + ': ' + name
							this.nextChapterUrl = OC.generateUrl('/apps/comicmode/?dir=' + this.parentDir + '/' + name)
						}
					}

				}, 500)

			} else {
				OCP.Toast.error(t('comicmode', data.reason))
			}
		},
		back() {
			window.location.replace(OC.generateUrl('/apps/files/?dir=' + this.dir))
		},
		sort(f1, f2) {
			const regex = new RegExp(/\d+/, 'g')
			let matches1 = [...f1.name.matchAll(regex)]
			if (matches1.length === 0) { matches1 = [[99999]] }
			let matches2 = [...f2.name.matchAll(regex)]
			if (matches2.length === 0) { matches2 = [[99999]] }
			const minlength = matches1.length > matches2.length ? matches2.length : matches1.length
			for (let i = 0; i < minlength; i++) {
				if (matches1[i][0] !== matches2[i][0]) {
					return matches1[i][0] - matches2[i][0]
				}
			}
			return 0
		},
		fullscreen() {
			if (this.imgMaxWidth === '600px') {
				this.icons.fullscreen = '/custom_apps/comicmode/img/fullscreen-exit-white.png'
				this.imgMaxWidth = '100%'
			} else {
				this.icons.fullscreen = '/custom_apps/comicmode/img/fullscreen-white.png'
				this.imgMaxWidth = '600px'
			}
		},
		up() {
			window.scrollTo(0, 0)
		},
		gotoPreviousChapter() {
			window.location.replace(this.previousChapterUrl)
		},
		gotoNextChapter() {
			window.location.replace(this.nextChapterUrl)
		},

	},
}
</script>
<style scoped>
.page{
    padding: 0px;
	width: 100%;
}

.title{
	font-size: 25px;
	text-align: center;
    padding: 10px;
}

.imglist div{
	text-align: center;
}

.imglist div .img{
	width: 100%;
	vertical-align: middle;
}

.icon{
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 40px;
    height: 40px;
	cursor: pointer;
	margin-bottom: 10px;
}

.icon img{
	width: 20px;
	height: 20px;
}

.white-icon{
	background-color: #343131;
}

.right-bottom{
	position: fixed;
	right: 20px;
	bottom: 60px;
}

.chapterbox{
	padding: 10px;
}

.textleft{
	text-align: left !important;
}

.textright{
	text-align: right !important;
}

.link{
	text-decoration: none;
	color: skyblue;
}

.rotate180{
	transform: rotate(180deg);
}

.gt800px{
	display: none;
}

@media only screen and (min-width: 600px) {
  .gt800px {
    display: flex;
  }
}

</style>
