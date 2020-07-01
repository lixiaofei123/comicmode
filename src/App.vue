<template>
	<div class="page">
		<div class="main">
			<div class="title">
				{{ title }}
			</div>
			<div class="imglist">
				<div v-if="hasImg">
					<div v-for="img in imglist" :key="img.name">
						<img :id="img.name"
							class="img"
							:style="{'max-width':imgMaxWidth}"
							:origin-src="img.url">
					</div>
					<div class="chapters">
						<div v-if="previousChapter" class="chapterbox textleft">
							<a class="link"
								href="javascript:void(0)"
								@click="gotoPreviousChapter()"
								v-text="t('comicmode', 'previous chapter') + ': ' + previousChapter.name" />
						</div>
						<div v-if="nextChapter" class="chapterbox textright">
							<a class="link"
								href="javascript:void(0)"
								@click="gotoNextChapter()"
								v-text="t('comicmode', 'next chapter') + ': ' + nextChapter.name" />
						</div>
					</div>
				</div>
				<div v-else>
					<div class="empty" v-text="t('comicmode', 'This folder is empty')" />
				</div>
			</div>
		</div>
		<div v-if="showCatalog" class="catalog fade-enter-width">
			<a href="#"
				title="close"
				class="app-sidebar__close icon-close"
				@click="closeCatalog()" />
			<div class="title">
				<h3 v-text="t('comicmode', 'chapter')" />
			</div>
			<ul>
				<li v-for="(chapter,index) in chapters" :key="chapter.name" class="item">
					<a :class="{'color_skybule' :chapter.isCurrent }" :href="chapter.url" v-text="(index + 1) + ' :' + chapter.name" />
				</li>
			</ul>
		</div>
		<div class="right-bottom">
			<div class="icon white-icon rotate180" @click="up()">
				<img :src="icons.download">
			</div>
			<div class="icon white-icon" @click="catalog()">
				<img :src="icons.catalog">
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
			imgMaxWidth: '600px',
			chapters: [],
			showCatalog: false,
			icons: {
				close: '/custom_apps/comicmode/img/close-white.png',
				download: '/custom_apps/comicmode/img/download-white.png',
				fullscreen: '/custom_apps/comicmode/img/fullscreen-white.png',
				catalog: '/custom_apps/comicmode/img/toggle-filelist-white.png',
			},
			imglist: [

			],
		}
	},
	computed: {
		hasImg() {
			return this.imglist.length !== 0
		},
		parentDir() {
			return this.dir.substring(0, this.dir.lastIndexOf('/'))
		},
		title() {
			return this.dir.substring(this.dir.lastIndexOf('/') + 1)
		},
		previousChapter() {
			const index = this.chapters.findIndex(c => c.isCurrent)
			if (index !== -1 && index !== 0) {
				return this.chapters[index - 1]
			}
			return undefined
		},
		nextChapter() {
			const index = this.chapters.findIndex(c => c.isCurrent)
			if (index !== -1 && index !== (this.chapters.length - 1)) {
				return this.chapters[index + 1]
			}
			return undefined
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
					let brothers = data.brothers
					if (brothers.length > 0) {
						brothers = brothers.sort(this.sort)
						for (let i = 0; i < brothers.length; i++) {
							const chapter = {
								name: brothers[i].name,
								url: OC.generateUrl('/apps/comicmode/?dir=' + this.parentDir + '/' + brothers[i].name),
							}
							if (brothers[i].name === this.title) {
								chapter.isCurrent = true
							}
							this.chapters.push(chapter)

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
			window.location.replace(this.previousChapter.url)
		},
		gotoNextChapter() {
			window.location.replace(this.nextChapter.url)
		},
		catalog() {
			this.showCatalog = !this.showCatalog
		},
		closeCatalog() {
			this.showCatalog = false
		},

	},
}
</script>
<style scoped>
.page{
    padding: 0px;
	width: 100%;
	display: flex;
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
	z-index: 2000;
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
	color: #28ADF6;
}

.rotate180{
	transform: rotate(180deg);
}

.gt800px{
	display: none;
}

.main{
	flex: 1;
}

.fade-enter-width{
	transition: width .5s;
}

.catalog{
	position: sticky;
	z-index: 1000;
	top: 50px;
	right: 0px;
	height: calc(100vh - 50px);
    width: 27vw;
    min-width: 300px;
    max-width: 500px;
	overflow-y: auto;
    overflow-x: hidden;
    background: var(--color-main-background);
    border-left: 1px solid var(--color-border);
}

.catalog .title{
	text-align: center;
}

.catalog ul,li{
	padding: 0px;
	margin: 0px;
	list-style: none;
}

.catalog .item{
	background-size: 16px 16px;
    background-position: 14px center;
    background-repeat: no-repeat;
    display: block;
    justify-content: space-between;
    line-height: 44px;
    min-height: 44px;
    padding: 0 12px 0 44px;
    overflow: hidden;
    box-sizing: border-box;
    white-space: nowrap;
    text-overflow: ellipsis;
    color: var(--color-main-text);
    opacity: 0.57;
    flex: 1 1 0px;
}

.app-sidebar__close{
	position: absolute;
    width: 44px;
    height: 44px;
    top: 0;
    right: 0;
    z-index: 100;
    opacity: .7;
    border-radius: 22px;
}

.app-sidebar__close:hover{
	opacity: 1;
    background-color: rgba(127,127,127,0.25);
}

.color_skybule{
	color: #28ADF6 !important;
}

.empty{
	text-align: center;
	font-size: 30px;
	padding-top: 100px;
	font-weight: 1000;
	color: grey;
}

@media only screen and (min-width: 600px) {
  .gt800px {
    display: flex;
  }
}

</style>
