import axios from '@nextcloud/axios'

export default {
	methods: {
		async saveRecord(record) {
			const response = await axios.post(OC.generateUrl(`/apps/comicmode/api/0.1/records`), record)
			return response.data
		},
		async deleteRecord(fileId) {
			const response = await axios.delete(OC.generateUrl(`/apps/comicmode/api/0.1/records/${fileId}`))
			return response.data
		},
		async listAllRecords() {
			const response = await axios.get(OC.generateUrl('/apps/comicmode/api/0.1/records'))
			return response.data
		},
	},
}
