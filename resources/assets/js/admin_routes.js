module.exports={

 routes: [
	{
		path: '/',
		component: require('./components/admin/Dashboard.vue')
	},
	{
		path: '/sections',
		component: require('./components/admin/Sections.vue')
	},
	{
		path: '/sections/:id',
		component: require('./components/admin/Section.vue'),
		children:[
			{
				path:'students',
				component: require('./components/admin/sections/students.vue')
			}
		]
	},
]

};