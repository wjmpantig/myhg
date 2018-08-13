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
		path:'/students/:id',
		component:require('./components/admin/students/Student.vue')
	},
	{
		path:'/transfer/:id',
		component:require('./components/admin/students/TransferStudent.vue')
	}
]

};