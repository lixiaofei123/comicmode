<template>
	<div class="page">
		<div v-if="page === 'comic'">
			<Content>
				<AppContent>
					<div class="main">
						<div class="title">
							{{ chapterName }}
						</div>
						<div class="imglist">
							<div v-if="hasImg">
								<div v-for="img in imglist" :key="img.name">
									<img :id="img.name"
										class="img"
										:style="{'max-width':imgMaxWidth}"
										:origin-src="img.url">
								</div>
							</div>
							<div v-else>
								<div class="empty" v-text="tips" />
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
					</div>
				</AppContent>
				<AppSidebar v-if="showCatalog" class="catalog" @close="closeCatalog">
					<div class="header">
						<div class="icon">
							<img :src="icons.app_blue">
						</div>
						<div class="chapterinfo">
							<div class="title" v-text="comicName" />
							<p v-text="t('comicmode','current {current} chapter / total {total} chapter',
								{current: currentChapter,total:chapterNums})" />
						</div>
						<div class="btns" />
					</div>
					<div class="content">
						<ul>
							<li v-for="(chapter,index) in chapters" :key="chapter.name" class="item">
								<a :class="{'color_skybule' :chapter.isCurrent }"
									href="javascript:void(0)"
									@click="goto(chapter.name)"
									v-text="(index + 1) + ' :' + chapter.name" />
							</li>
						</ul>
					</div>
				</AppSidebar>
			</Content>
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
		<div v-if="page === 'record'">
			<Content>
				<AppNavigation>
					<AppNavigationItem :title="t('comicmode','Read history')" icon="icon-category-enabled" />
				</AppNavigation>
				<AppContent>
					<AppContentDetails>
						<div class="records-area">
							<div v-for="record in records" :key="record.id" class="activity">
								<div class="activity-title"
									v-text="t('comicmode','<{chapterName}> of <{bookName}>',
										{chapterName:record.chapterName,bookName:record.comicName})" />
								<div class="activity-action">
									<Actions>
										<ActionButton icon="icon-delete" @click="deleteRecordData(record)" v-text="t('comicmode','Delete')" />
									</Actions>
									<Actions>
										<ActionButton icon="icon-toggle" @click="view(record)" v-text="t('comicmode','Read')" />
									</Actions>
								</div>
								<div class="activity-time" v-text="beautyTime(record.lastReadTime)" />
							</div>
						</div>
					</AppContentDetails>
				</AppContent>
			</Content>
		</div>
	</div>
</template>
<script>
import ImgLazyProcessor from 'img-lazy-processor'

import axios from '@nextcloud/axios'
import AppSidebar from '@nextcloud/vue/dist/Components/AppSidebar'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import Content from '@nextcloud/vue/dist/Components/Content'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppContentDetails from '@nextcloud/vue/dist/Components/AppContentDetails'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import Actions from '@nextcloud/vue/dist/Components/Actions'

import recordApi from './mixins/record_api'
import utils from './mixins/utils'

export default {
	name: 'App',
	components: {
		AppSidebar,
		AppContent,
		Content,
		AppNavigation,
		AppNavigationItem,
		AppContentDetails,
		Actions,
		ActionButton,
	},
	mixins: [recordApi, utils],
	data() {
		return {
			page: '',
			dir: '',
			imgMaxWidth: '600px',
			tips: '',
			chapterName: '',
			chapters: [],
			showCatalog: false,
			records: [],
			icons: {
				app_blue: '/custom_apps/comicmode/img/app_blue.svg',
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
		chapterNums() {
			return this.chapters.length
		},
		currentChapter() {
			return this.chapters.findIndex(c => c.isCurrent) + 1
		},
		title() {
			return this.dir.substring(this.dir.lastIndexOf('/') + 1)
		},
		comicName() {
			const comicName1 = this.parentDir.substring(this.parentDir.lastIndexOf('/') + 1)
			return comicName1 || t('comicmode', 'unkown comic name')

		},
		previousChapter() {
			if (this.currentChapter > 1) {
				return this.chapters[this.currentChapter - 2]
			}
			return undefined
		},
		nextChapter() {
			if (this.currentChapter !== 0 && this.currentChapter !== this.chapters.length) {
				return this.chapters[this.currentChapter]
			}
			return undefined
		},

	},
	mounted() {
		this.init()
	},
	methods: {
		async init() {
			this.page = document.getElementsByName('page')[0].value
			if (this.page === 'comic') {
				this.dir = document.getElementsByName('dir')[0].value
				this.tips = t('comicmode', 'Trying to load')

				if (this.dir.endsWith('/')) {
					this.dir = this.dir.substring(0, this.dir.length - 1)
				}
				this.btitle = document.title
				this.loadData(this.title)
			} else if (this.page === 'record') {
				this.loadRecords()
			}

		},
		async loadRecords() {
			this.listAllRecords().then(records => {
				this.records = records
			}).catch(() => {
				console.error('load read record failed')
			})
		},
		async loadData(dirname) {
			this.tips = t('comicmode', 'Trying to load')
			const url = OC.generateUrl('/apps/comicmode/api/1.0/list.json?dir=' + this.parentDir + '/' + dirname)
			document.title = dirname + '-' + this.btitle
			this.chapterName = dirname

			const resp = await axios.get(url)
			const data = resp.data
			const status = data.status

			if (status === 'OK') {
				this.loadChapters(data, dirname)
				this.loadImages(data)
			} else {
				OCP.Toast.error(t('comicmode', data.reason))
			}
		},
		readRecord() {
			this.saveRecord({
				fileId: this.parendId,
				comicName: this.comicName,
				chapterName: this.chapterName,
				comicDir: this.parentDir,
			}).catch(() => {
				console.error('阅读记录记录失败')
			})
		},
		loadImages(data) {
			this.imglist = []
			let files = data.files
			if (files.length !== 0) {
				this.readRecord()
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
				}, 1000)
			} else {
				this.tips = t('comicmode', 'This folder is empty')
			}

		},
		loadChapters(data, dirname) {
			this.chapters = []
			let brothers = data.brothers
			if (brothers.length > 0) {
				brothers = brothers.sort(this.sort)
				for (let i = 0; i < brothers.length; i++) {
					const chapter = {
						name: brothers[i].name,
					}
					if (brothers[i].name === dirname) {
						chapter.isCurrent = true
					}
					this.chapters.push(chapter)
				}
			}
			this.parendId = data.parendId
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
			this.goto(this.previousChapter.name)
		},
		gotoNextChapter() {
			this.goto(this.nextChapter.name)
		},
		goto(name) {
			this.loadData(name)
		},
		catalog() {
			this.showCatalog = !this.showCatalog
		},
		closeCatalog() {
			this.showCatalog = false
		},
		deleteRecordData(record) {
			this.deleteRecord(record.fileId).then(record => {
				const index = this.records.findIndex(r => r.id === record.id)
				if (index !== -1) {
					this.records.splice(index, 1)
				}
			}).catch(() => {
				OCP.Toast.error(t('comicmode', 'Delete is not Success'))
			})
		},
		view(record) {
			const dir = record.comicDir + '/' + record.chapterName
			document.getElementsByName('dir')[0].value = dir
			document.getElementsByName('page')[0].value = 'comic'
			this.init()
		},
	},
}
</script>
<style scoped>
#content{
	padding-top: 20px;
}

.records-area{
	padding: 15px 15px 15px 20px;
}

.activity{
	width:100%;
	display: flex;
	padding: 10px 0px;
	border-bottom:1px solid  var(--color-border);
}

.activity .activity-title{
	flex: 1;
	line-height: 40px;
}

.activity .activity-action{
	width:120px;
}

.activity .activity-time{
	width:120px;
	line-height: 40px;
}

.page{
    padding: 0px;
	width: 100%;
}

.main .title{
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
	padding-bottom:20px;
}

.fade-enter-width{
	transition: width .5s;
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

 .catalog {
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
	border-left: 1px solid var(--color-border);
	background: var(--color-main-background);
}

.header {
	padding: 15px 5px;
	display: flex;
	border-bottom: 1px solid var(--color-border);
}

.header .icon,
.header img {
	width: 48px;
	height: 48px;
}

.header .btns {
	width: 120px;
	height: 30px;
}

.header .chapterinfo {
	width: calc(100% - 60px);
	padding-left: 15px;
	flex: 1 1;
}

.header .chapterinfo .title {
	font-weight: bold;
	font-size: 20px;
	line-height: 30px;
	text-align: left;
}

.header .chapterinfo p {
	font-size: 14px;
	padding: 0;
	opacity: 0.7;
	width: 100%;
	white-space: nowrap;
	text-overflow: ellipsis;
	overflow: hidden;
	margin: 0;
}

.catalog .title {
	text-align: center;
}

.catalog ul,
li {
	padding: 0px;
	margin: 0px;
	list-style: none;
}

.catalog .content {
	padding: 10px 20px;
}

.catalog .item {
	background-size: 16px 16px;
	background-position: 14px center;
	background-repeat: no-repeat;
	display: block;
	justify-content: space-between;
	line-height: 30px;
	min-height: 30px;
	overflow: hidden;
	box-sizing: border-box;
	white-space: nowrap;
	text-overflow: ellipsis;
	opacity: 0.57;
	flex: 1 1 0px;
	transition: background-color 0.3s ease;
	height: 40px;
	color: var(--color-main-text);
}

.catalog .item a {
	border: 0;
	color: var(--color-main-text);
	text-decoration: none;
	cursor: pointer;
}

.catalog .item a:hover{
	color:black;
}

@media only screen and (min-width: 600px) {
  .gt800px {
    display: flex;
  }
}

</style>
