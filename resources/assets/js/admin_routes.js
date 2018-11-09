module.exports={

 routes: [
	{
		path: '/',
		component: require('./components/admin/Dashboard.vue')
	},
	{
		path: '/sections',
		component: require('./components/admin/sections/Sections.vue')
	},
	{
		path: '/sections/:id',
		component: require('./components/admin/sections/Section.vue'),
		children:[
			{
				path:'',
				component: require('./components/admin/sections/students.vue')
			},
			{
				path:'attendance',
				component: require('./components/admin/sections/Attendance.vue')
			},
			{
				path:'homeworks',
				component: require('./components/admin/sections/Scores.vue'),
				meta:{
					type_id: 1
				}
			},
			{
				path:'quizzes',
				component: require('./components/admin/sections/Scores.vue'),
				meta:{
					type_id: 2
				}
			},
			{
				path:'finals',
				component: require('./components/admin/sections/Scores.vue'),
				meta:{
					type_id: 3
				}
			}
		]
	},
	{
		path: '/students',
		component: require('./components/admin/students/Students.vue')
	},
	{
		path: '/students/new',
		component: require('./components/admin/students/NewStudent.vue')
	},
	{
		path:'/students/:id/transfer',
		component:require('./components/admin/students/TransferStudent.vue')
	},
	{
		path:'/students/:id',
		component:require('./components/admin/students/Student.vue')
	},
	{
		path:'/settings/',
		component:require('./components/admin/Settings.vue')
	},
	{
		path:'/users/create',
		component:require('./components/admin/users/CreateUser.vue')
	},
	{
		path:'/export/',
		component:require('./components/admin/export/Export.vue'),
		children:[
			{
				path:'',
				component:require('./components/admin/export/ForPrint.vue'),
			},
			{
				path:'grades',
				component:require('./components/admin/export/Grades.vue'),
			}
		]
	},
	{
		path:'*',
		component:require('./components/404.vue')
	},
]

};